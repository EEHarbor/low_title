#!/usr/bin/env bash
set -uo pipefail
root="$(cd "$(dirname "$0")/../.." && pwd)"
report_dir="$(mktemp -d "$root/tests/reports/matrix-XXXXXXXX")"
for suffix in 74 80 81 82 83 84 85; do
    runtime="/Users/tomjaeger/Library/Application Support/Herd/bin/php$suffix"
    "$runtime" -r 'echo PHP_VERSION;' > "$report_dir/php$suffix.version"
    if LOW_TITLE_PHP_BIN="$runtime" "$root/tests/.phpunit/run-phpunit.sh" -c "$root/phpunit.xml" --log-junit "$report_dir/php$suffix.xml" > "$report_dir/php$suffix.txt" 2>&1; then
        printf '0' > "$report_dir/php$suffix.exit"
    else
        printf '%s' "$?" > "$report_dir/php$suffix.exit"
    fi
    tail -2 "$report_dir/php$suffix.txt"
done
python3 "$root/tests/scripts/summarize-matrix.py" "$report_dir" "$root/tests/reports/matrix-results.json"
result=$?
printf 'REPORT_DIR=%s\n' "$report_dir"
exit "$result"
