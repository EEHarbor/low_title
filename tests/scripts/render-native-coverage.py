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
    'extract-checksum': [('@stub', 'extractFile')],
    'extract-size': [('@stub', 'extractFile')],
    'extract-truncated': [('@stub', 'extractFile')],
}
for version, implementation in [(2, 2), (3, 3), (4, 4), (5, 5), (6, 5), (7, 5)]:
    targets[f'bridge-ee{version}'] = [('Bridge/EE', '__construct'), (f'Bridge/EE{implementation}', 'getAddonThemesDir')]
for case in ['legacy-config', 'legacy-defined']:
    targets['loading-' + case] = [('Bridge/EE', 'ee'), ('Bridge/EE', '__construct')]
for case in ['system', 'app', 'priority', 'defined-version', 'installer', 'installer-ancestor', 'predeclared']:
    targets['loading-fieldtype-' + case] = [('Base/Ft', '{main}')]
for case in ['unrelated', 'sibling-namespace', 'missing-archive', 'missing-class', 'native', 'opcache', 'extraction', 'no-opcache-api', 'restricted-opcache-api']:
    targets['autoload-' + case] = [('@autoload', '{closure}')]
targets['autoload-extraction'] += [('@stub', 'go'), ('@stub', '_unpack'), ('@stub', 'tmpdir'), ('@stub', '_removeTmpFiles')]
for case in ['named-default', 'global-explicit', 'global-default']:
    targets['pagination-' + case] = [('Base/Mcp', 'paginateModel'), ('Bridge/EE5', 'moduleURL'), ('Bridge/EE5', 'pagination')]
for case in ['plain', 'depth', 'mode', 'tmp-file', 'tmp-readonly', 'missing', 'file', 'empty']:
    targets['session-' + case] = [('@stub', 'go'), ('@stub', 'tmpdir')]
nav_methods = [method['name'] for method in inventory['files']['low_title/low_title.phar/FluxCapacitor/Conduit/McpNav.php']['functions']]
for case in ['cold-provider', 'modern', 'legacy', 'http-route', 'native-properties']:
    targets['navigation-' + case] = [('Conduit/McpNav', '{main}')] + [('Conduit/McpNav', method) for method in nav_methods]
for case in ['cold-provider', 'modern']:
    targets['navigation-' + case] += [('Base/Mcp', '__construct'), ('Base/Mcp', 'canIncludeNav')]
lines = ['# Native coverage contexts', '', f"Production baseline: `{inventory['baseline']}`. These fresh processes load the shipped PHAR with explicit EE versions, request contexts, and strict E_ALL diagnostics. Uninstall behavior uses a declared-property subject; unmodified constructors retain separate compatibility regressions. Each context remains independent of the parent suite and every other native graph. No counts below are added to parent coverage or used to close a source unit.", '', '| Context | REQ / EE | PHP / Xdebug | Process exit | Method | Lines hit / executable | Blocks hit / total | Exits hit / total | Uncovered lines |', '|---|---|---|---:|---|---:|---:|---:|---|']
for context, methods in targets.items():
    raw = json.loads((reports / f'{context}.raw.json').read_text())
    summary = json.loads((reports / f'{context}.branches.json').read_text())
    exit_code = int((reports / f'{context}.exit').read_text())
    for member, name in methods:
        relative = None
        origin = 'low_title/low_title.phar#stub'
        physical = root / 'low_title/low_title.phar'
        if member == '@autoload':
            origin = 'low_title/autoload.php'
            physical = root / origin
        if member not in ['@stub', '@autoload']:
            relative = 'index.php' if member == '@index' else 'FluxCapacitor/' + member + '.php'
            origin = 'low_title/low_title.phar/' + relative
        graphs = [item for item in summary['methods'] if item['file'] == origin and (item['function'] == name or item['function'].endswith('->' + name) or name == '{closure}' and '{closure:' in item['function'])]
        graph = graphs[0] if len(graphs) == 1 else None
        source_line = None if graph is None else graph['start_line']
        if member == 'Bridge/EE' and name == '__construct':
            source_line = raw.get('context', {}).get('bridge_constructor', {}).get('start_line')
        declarations = [item for item in inventory['files'][origin]['functions'] if item['name'] == name and (source_line is None or item['line'] <= source_line <= item['end_line'])]
        method = declarations[0] if len(declarations) == 1 else None
        if graph is not None and graph.get('attribution_matches') is False:
            graph = None
        source = raw['files'].get(str(physical))
        if relative is not None:
            source = next((item for path, item in raw['files'].items() if path.endswith('/' + relative)), None)
        covered = None if source is None or method is None else {int(line): count for line, count in source['lines'].items() if method['line'] <= int(line) <= method['end_line'] and count >= -1}
        if name == '{main}' and source is not None:
            functions = inventory['files'][origin]['functions']
            covered = {int(line): count for line, count in source['lines'].items() if count >= -1 and not any(item['line'] <= int(line) <= item['end_line'] for item in functions)}
            if graph is not None and not graph['blocks_hit']:
                graph = None
        line_counts = 'unknown' if covered is None else f"{sum(count > 0 for count in covered.values())}/{len(covered)}"
        blocks = 'unknown' if graph is None else f"{graph['blocks_hit']}/{graph['blocks']}"
        exits = 'unknown' if graph is None else f"{graph['exits_hit']}/{graph['exits']}"
        missing = 'unknown' if covered is None else ', '.join(str(line) for line, count in covered.items() if count <= 0) or 'none'
        request = raw['context']['request']
        version = raw['context']['ee_version']
        lines.append(f"| `{context}` | {request} / {version} | {raw['runtime']} / {raw['xdebug']} | {exit_code} | `{member}::{name}` | {line_counts} | {blocks} | {exits} | {missing} |")
lines += ['', 'Raw JSON, retained out/out_hit graphs, stdout/stderr, exit status, and the generated summary are stored in the unique native report directory. The checksum rejection intentionally terminates with its diagnostic and legacy exit status 0. Size and truncated-stream contexts exit 1 because the shipped size-error expression reads undefined $stat at stub line 262; strict diagnostics remain visible. The measured Xdebug 3.1.6 and 3.5.3 contexts attribute conditional Bridge/EE constructor graphs to the first declaration; Reflection identifies the actual runtime method. Mismatched graph ranges remain preserved in the raw/normalized report and have unknown per-method branch metrics above. Uninstrumented native contexts remain unknown. Default loading without an optional add-on navigation class remains a source diagnostic, while the separate provider contexts below execute supported inclusion paths. Native source diagnostics remain ordinary failures in the complete matrix.', '']
lines += ['Loading probes omit bootstrap-owned `ee()` and fieldtype parent declarations. The legacy contexts use EE2.5 and verify the shipped `get_instance()` fallback plus configured/predefined version precedence. Fieldtype paths receive a byte-identical copy of the existing bounded `Support/Fieldtype.php` contract; these are native add-on loader checks, not full EE-core integration. Their procedural rows select source lines outside inventoried method ranges. Xdebug zero-hit procedural graphs alongside executed lines remain unknown branch metrics, with raw counters preserved. The installer-ancestor case exits 1 because global replacement at Ft line 13 also changes the ancestor directory, preventing the parent load at line 35. Every context removes its unique fixture files before reporting.', '']
lines += ['Autoloader probes report the actual availability of opcache_get_configuration. The configured PHP 7.4 runtime has no opcode-cache API, so its opcache flag case exercises the supported fallback; PHP 8.2 separately measures the available API and validate_permission query-string branch. The disabled-API case uses only command-local configuration. A restricted API raises the source warning at autoload line 77 on runtimes where the API exists. Full extraction still stops on its strict mkdir diagnostic. Pagination probes join the declared-property behavior subject to the actual bridge and typed bounded CP/Pagination collaborator. A global call with its default null URL forwards an empty string, violating the real render(Url) contract; its type-boundary error retains the shipped caller trace. Controller/default and global/explicit URL controls pass. Every raw context remains separate, including different runtimes.', '']
lines += ['Session fallback probes first verify that the actual PHP files handler accepts each valid configured path and creates its owned mode-0600 session file. The three-part N;MODE;/path form is valid PHP configuration, but this stub strips only the first separator and terminates at line 159. An empty session.save_path also accepts the owned sys_temp_dir through the real files handler, yet the stub rejects it instead of using that default. Its legacy exit status 0 and verbatim output remain in the JSON alongside returned=false; the ordinary valid-path test remains red. Plain/depth and read-only-temp fallback reach the existing mkdir warning. A writable file as TMPDIR reaches a distinct line-167 Not a directory diagnostic despite a valid session fallback. Missing and file-valued session locations intentionally reject with the preserved diagnostic. The valid empty-default case remains an ordinary red regression with its native default-directory proof. Shutdown cleanup captures output and restores owned directory permissions even after source die(); parent cleanup remains independent. No session native graph is merged with other contexts.', '']
lines += ['Optional navigation uses an ordinary extension-provider file in a unique owned installation linking to the original PHAR and metadata. A cold controller context discovers that file through canIncludeNav; the modern scenario context preloads it before exercising configured hooks. Behavior providers declare flux, and the MCP behavior subject declares its version/nav slots, allowing ordinary algorithms without hiding native diagnostics. A separate provider subclass leaves flux undeclared and retains the original line-32 PHP 8.2+ regression. The internal controller route containing http is observed as the original wrong raw string; its PHPUnit assertion remains red even though the observation probe exits 0. Modern/legacy/native compatibility contexts and runtimes remain separate. URL/header/sidebar/breadcrumb collaborators match 22 actual-core values on all seven runtimes; route formatting, view/CP constructor dependencies and full HTML/application integration remain bounded. The five navigation PHPUnit cases assert the original PHAR hash, source/provider Reflection filenames and owned cleanup without following symlinks. FluxCapacitor::L remains false, leaving license injection unavailable.', '']
pathlib.Path(sys.argv[2]).write_text('\n'.join(lines))
