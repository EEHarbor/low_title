#!/usr/bin/env bash
set -euo pipefail
root="$(cd "$(dirname "$0")/../.." && pwd)"
runtime="${LOW_TITLE_PHP_BIN:-/Users/tomjaeger/Library/Application Support/Herd/bin/php74}"
mode="${1:-lines}"
report_dir="$(mktemp -d "$root/tests/reports/coverage-XXXXXXXX")"
extension="${LOW_TITLE_XDEBUG_EXTENSION:-/Applications/Herd.app/Contents/Resources/xdebug/xdebug-74-arm64.so}"
"$root/tests/.phpunit/verify.sh" phpunit-9.phar
printf 'REPORT_DIR=%s\n' "$report_dir"
if [[ "$mode" == 'lines' ]]; then
    LOW_TITLE_COVERAGE_SOURCE="$root/tests/.source" exec "$runtime" -d error_reporting=-1 -d display_errors=stderr -d "zend_extension=$extension" -d xdebug.mode=coverage "$root/tests/.phpunit/phpunit-9.phar" -c "$root/phpunit.xml" --coverage-clover "$report_dir/clover.xml"
fi
if [[ "$mode" == 'branches' ]]; then
    LOW_TITLE_COVERAGE_SOURCE= LOW_TITLE_BRANCH_REPORT="$report_dir/raw.json" exec "$runtime" -d "auto_prepend_file=$root/tests/Support/BranchFilter.php" -d error_reporting=-1 -d display_errors=stderr -d "zend_extension=$extension" -d xdebug.mode=coverage "$root/tests/.phpunit/phpunit-9.phar" -c "$root/phpunit.xml" --no-coverage
fi
printf 'Unknown coverage mode: %s\n' "$mode" >&2
exit 1
