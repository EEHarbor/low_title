#!/usr/bin/env python3
"""Summarize complete fresh JUnit runs; retain every failure attribution."""
import json
import pathlib
import re
import sys
import xml.etree.ElementTree as ET

report_dir = pathlib.Path(sys.argv[1])
summary = []
for suffix in ('74', '80', '81', '82', '83', '84', '85'):
    prefix = report_dir / f'php{suffix}'
    tree = ET.parse(prefix.with_suffix('.xml'))
    cases = list(tree.iter('testcase'))
    if not cases:
        raise SystemExit(f'No tests executed for PHP {suffix}')
    issues = []
    counts = dict(tests=len(cases), assertions=sum(int(case.get('assertions', 0)) for case in cases), errors=0, failures=0, warnings=0, risky=0, skipped=0, incomplete=0)
    for case in cases:
        for element in case:
            kind = element.tag
            text = element.text or ''
            if kind not in ('error', 'failure', 'skipped'):
                continue
            category = {'error': 'errors', 'failure': 'failures', 'skipped': 'skipped'}[kind]
            if 'RiskyTest' in element.get('type', ''):
                category = 'risky'
            if 'Warning' in element.get('type', ''):
                category = 'warnings'
            if 'IncompleteTest' in element.get('type', ''):
                category = 'incomplete'
            counts[category] += 1
            issues.append(dict(test=case.get('name'), class_name=case.get('class'), category=category, type=element.get('type'), message=re.sub(r'Object &[0-9a-f]+', 'Object &<id>', '\n'.join(text.splitlines()[:3]))))
    version = prefix.with_suffix('.version').read_text()
    row = dict(runtime=version, phpunit='9.6.38', command=f'LOW_TITLE_PHP_BIN=/Users/tomjaeger/Library/Application Support/Herd/bin/php{suffix} tests/.phpunit/run-phpunit.sh -c phpunit.xml --log-junit <unique-report-directory>/php{suffix}.xml', exit_code=int(prefix.with_suffix('.exit').read_text()), **counts, issues=issues)
    summary.append(row)
    print(version, json.dumps(counts))
pathlib.Path(sys.argv[2]).write_text(json.dumps({'reports_directory': str(report_dir), 'runs': summary}, indent=2) + '\n')
sys.exit(1 if any(run['exit_code'] for run in summary) else 0)
