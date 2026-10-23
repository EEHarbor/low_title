#!/usr/bin/env python3
"""Join source inventory, Clover lines, and genuine Xdebug exits without inferring missing branches."""
import json
import pathlib
import sys
import xml.etree.ElementTree as ET

root = pathlib.Path(__file__).resolve().parents[2]
inventory = json.loads((root / 'tests/source-inventory.json').read_text())
branches = json.loads(pathlib.Path(sys.argv[2]).read_text())
files = {}
for node in ET.parse(sys.argv[1]).iter('file'):
    path = node.get('name')
    if '/tests/.source/' in path:
        origin = 'low_title/low_title.phar/' + path.split('/tests/.source/')[1]
    else:
        origin = path.removeprefix(str(root) + '/')
    files[origin] = {int(line.get('num')): int(line.get('count')) for line in node.findall('line') if line.get('type') == 'stmt'}
for source in branches.get('sources', []):
    if source['file'] not in files:
        files[source['file']] = {int(line): int(count > 0) for line, count in source['lines'].items() if count >= -1}
rows = []
for origin, source in inventory['files'].items():
    lines = files.get(origin)
    methods = []
    for method in source['functions']:
        covered_lines = None if lines is None else {line: count for line, count in lines.items() if method['line'] <= line <= method['end_line']}
        graphs = [graph for graph in branches['methods'] if graph.get('attribution_matches') is not False and graph['file'] == origin and method['line'] <= graph['start_line'] <= method['end_line'] and (graph['function'].endswith('->' + method['name']) or graph['function'] == method['name'] or method['name'] == '{closure}' and '{closure:' in graph['function'])]
        graph = graphs[0] if len(graphs) == 1 else None
        methods.append(dict(name=method['name'], start_line=method['line'], end_line=method['end_line'], executable=method['executable'], lines=None if covered_lines is None else len(covered_lines), lines_hit=None if covered_lines is None else sum(bool(count) for count in covered_lines.values()), uncovered_lines=None if covered_lines is None else [line for line, count in covered_lines.items() if not count], xdebug_blocks=None if graph is None else graph['blocks'], xdebug_blocks_hit=None if graph is None else graph['blocks_hit'], xdebug_exits=None if graph is None else graph['exits'], xdebug_exits_hit=None if graph is None else graph['exits_hit'], uncovered_exits=None if graph is None else graph['uncovered_exits'], completion='not executable' if not method['executable'] else 'incomplete' if graph is None or covered_lines is None or not covered_lines or any(not count for count in covered_lines.values()) or graph['exits_hit'] != graph['exits'] else 'measured target'))
    rows.append(dict(file=origin, sha256=source['sha256'], executable_lines=None if lines is None else len(lines), covered_lines=None if lines is None else sum(bool(count) for count in lines.values()), uncovered_lines=None if lines is None else [line for line, count in lines.items() if not count], procedural_branch_metrics='native/parent graphs kept separate' if source['procedural'] else 'not applicable: declarations only', procedural_graphs=[graph for graph in branches['methods'] if graph['file'] == origin and graph['function'] == '{main}'] if source['procedural'] else [], methods=methods))
output = dict(baseline=inventory['baseline'], runtime=branches['runtime'], xdebug=branches['xdebug'], notes=['Clover provides lines only. Xdebug out/out_hit provides actual opcode exits, including fixed nonempty-loop edges and synthetic return sentinels. Missing branch data is unknown.', 'Native subprocesses execute shipped archives with diagnostics enforced, but their graphs are not merged into this parent graph. Unloaded/unexecuted source remains in this inventory.', 'The skill helper incorrectly labels null branch metrics at_target; its labels are not used as campaign completion.'], files=rows)
pathlib.Path(sys.argv[3]).write_text(json.dumps(output, indent=2) + '\n')
for row in rows:
    print(row['file'], str(row['covered_lines']) + '/' + str(row['executable_lines']))
