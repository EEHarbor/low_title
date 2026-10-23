#!/usr/bin/env python3
"""Report actual Xdebug basic blocks and exits without treating Clover as branches."""
import json
import pathlib
import sys

root = pathlib.Path(__file__).resolve().parents[2]
data = json.loads(pathlib.Path(sys.argv[1]).read_text())
rows = []
sources = []
for path, source in data['files'].items():
    if path.startswith('phar://' + str(root / 'low_title/low_title.phar') + '/'):
        origin = path.removeprefix('phar://' + str(root) + '/')
    elif path.startswith(str(root / 'low_title') + '/'):
        origin = path.removeprefix(str(root) + '/')
    else:
        continue
    if origin == 'low_title/low_title.phar':
        origin += '#stub'
    sources.append({'file': origin, 'lines': source.get('lines', {})})
    for function, graph in source.get('functions', {}).items():
        blocks = graph.get('branches', {})
        if isinstance(blocks, list):
            blocks = dict(enumerate(blocks))
        exits = []
        missing_blocks = []
        for block_id, block in blocks.items():
            if not block['hit']:
                missing_blocks.append([block['line_start'], block['line_end']])
            targets = block.get('out', {})
            hits = block.get('out_hit', {})
            if isinstance(targets, list):
                targets = dict(enumerate(targets))
            if isinstance(hits, list):
                hits = dict(enumerate(hits))
            for slot, target in targets.items():
                exits.append({'block': block_id, 'slot': slot, 'target': target, 'line': block['line_end'], 'hit': hits.get(slot, hits.get(str(slot), 0))})
        rows.append({'file': origin, 'function': function, 'start_line': min((block['line_start'] for block in blocks.values()), default=0), 'end_line': max((block['line_end'] for block in blocks.values()), default=0), 'blocks': len(blocks), 'blocks_hit': len(blocks)-len(missing_blocks), 'exits': len(exits), 'exits_hit': sum(bool(exit['hit']) for exit in exits), 'uncovered_blocks': missing_blocks, 'uncovered_exits': [exit for exit in exits if not exit['hit']]})
result = {'runtime': data['runtime'], 'xdebug': data['xdebug'], 'metrics': 'Xdebug opcode blocks and out/out_hit exits; no cross-runtime/graph merging', 'methods': rows, 'sources': sources}
output = pathlib.Path(sys.argv[2])
output.write_text(json.dumps(result, indent=2) + '\n')
for row in rows:
    if row['file'] == 'low_title/pi.low_title.php':
        print(f"{row['function']}: blocks {row['blocks_hit']}/{row['blocks']}; exits {row['exits_hit']}/{row['exits']}")
