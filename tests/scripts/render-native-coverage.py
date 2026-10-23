#!/usr/bin/env python3
"""Render each native request/version graph without combining coverage contexts."""
import json
import pathlib
import sys

root = pathlib.Path(__file__).resolve().parents[2]
reports = pathlib.Path(sys.argv[1])
inventory = json.loads((root / 'tests/source-inventory.json').read_text())
targets = {
    'construct-Base-Mcp': [('Base/Mcp', '__construct'), ('Base/Mcp', 'canIncludeNav')],
    'construct-Base-Tab': [('Base/Tab', '__construct'), ('Conduit/Version', 'check')],
    'construct-Base-Ft': [('Base/Ft', '__construct'), ('Conduit/Version', 'check')],
    'extension-menu': [('Base/Ext', 'cpCustomMenu')],
    'archive-index': [('@index', '{closure}')],
    'updater-ee2': [('Base/Upd', 'uninstall')],
    'updater-ee5': [('Base/Upd', 'uninstall')],
    'updater-ee6': [('Base/Upd', 'uninstall')],
}
for version, implementation in [(2, 2), (3, 3), (4, 4), (5, 5), (6, 5), (7, 5)]:
    targets[f'bridge-ee{version}'] = [('Bridge/EE', '__construct'), (f'Bridge/EE{implementation}', 'getAddonThemesDir')]
lines = ['# Native coverage contexts', '', f"Production baseline: `{inventory['baseline']}`. These fresh processes load the shipped PHAR with explicit EE versions, request contexts, and strict E_ALL diagnostics. Uninstall behavior uses a declared-property subject; unmodified constructors retain separate compatibility regressions. Each context remains independent of the parent suite and every other native graph. No counts below are added to parent coverage or used to close a source unit.", '', '| Context | REQ / EE | PHP / Xdebug | Process exit | Method | Lines hit / executable | Blocks hit / total | Exits hit / total | Uncovered lines |', '|---|---|---|---:|---|---:|---:|---:|---|']
for context, methods in targets.items():
    raw = json.loads((reports / f'{context}.raw.json').read_text())
    summary = json.loads((reports / f'{context}.branches.json').read_text())
    exit_code = int((reports / f'{context}.exit').read_text())
    for member, name in methods:
        relative = 'index.php' if member == '@index' else 'FluxCapacitor/' + member + '.php'
        origin = 'low_title/low_title.phar/' + relative
        graphs = [item for item in summary['methods'] if item['file'] == origin and (item['function'].endswith('->' + name) or name == '{closure}' and '{closure:' in item['function'])]
        graph = graphs[0] if len(graphs) == 1 else None
        source_line = None if graph is None else graph['start_line']
        if member == 'Bridge/EE' and name == '__construct':
            source_line = raw.get('context', {}).get('bridge_constructor', {}).get('start_line')
        declarations = [item for item in inventory['files'][origin]['functions'] if item['name'] == name and (source_line is None or item['line'] <= source_line <= item['end_line'])]
        method = declarations[0] if len(declarations) == 1 else None
        if graph is not None and graph.get('attribution_matches') is False:
            graph = None
        source = next((item for path, item in raw['files'].items() if path.endswith('/' + relative)), None)
        covered = None if source is None or method is None else {int(line): count for line, count in source['lines'].items() if method['line'] <= int(line) <= method['end_line'] and count >= -1}
        line_counts = 'unknown' if covered is None else f"{sum(count > 0 for count in covered.values())}/{len(covered)}"
        blocks = 'unknown' if graph is None else f"{graph['blocks_hit']}/{graph['blocks']}"
        exits = 'unknown' if graph is None else f"{graph['exits_hit']}/{graph['exits']}"
        missing = 'unknown' if covered is None else ', '.join(str(line) for line, count in covered.items() if count <= 0) or 'none'
        request = raw['context']['request']
        version = raw['context']['ee_version']
        lines.append(f"| `{context}` | {request} / {version} | {raw['runtime']} / {raw['xdebug']} | {exit_code} | `{member}::{name}` | {line_counts} | {blocks} | {exits} | {missing} |")
lines += ['', 'Raw JSON, retained out/out_hit graphs, stdout/stderr, exit status, and the generated summary are stored in the unique native report directory. Xdebug 3.1.6 attributes every conditional Bridge/EE constructor graph to its first declaration; Reflection identifies the actual runtime method. Mismatched graph ranges remain preserved in the raw/normalized report and have unknown per-method branch metrics above. Uninstrumented native contexts remain unknown. The absent optional add-on navigation class keeps its successful-inclusion paths unavailable; source diagnostics still stop native constructors on PHP >= 8.2 in the complete matrix.', '']
pathlib.Path(sys.argv[2]).write_text('\n'.join(lines))
