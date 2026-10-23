#!/usr/bin/env bash
set -euo pipefail
root="$(cd "$(dirname "$0")/../.." && pwd)"
runtime="${LOW_TITLE_PHP_BIN:-/Users/tomjaeger/Library/Application Support/Herd/bin/php74}"
extension="${LOW_TITLE_XDEBUG_EXTENSION:-/Applications/Herd.app/Contents/Resources/xdebug/xdebug-74-arm64.so}"
report_dir="$(mktemp -d "$root/tests/reports/native-coverage-XXXXXXXX")"
status=0
printf 'REPORT_DIR=%s\n' "$report_dir"
while IFS='|' read -r label request ee_version case_name; do
    temp_dir="$(mktemp -d "$report_dir/temp-XXXXXXXX")"
    script="$root/tests/scripts/native.php"
    arguments=("$case_name")
    if [[ "$label" == loading-* ]]; then
        script="$root/tests/scripts/loading-probe.php"
        arguments+=("$temp_dir")
    fi
    if LOW_TITLE_COVERAGE_SOURCE= LOW_TITLE_EE_REQUEST="$request" LOW_TITLE_EE_VERSION="$ee_version" LOW_TITLE_BRANCH_REPORT="$report_dir/$label.raw.json" TMPDIR="$temp_dir" "$runtime" -d error_reporting=-1 -d display_errors=stderr -d "sys_temp_dir=$temp_dir" -d "zend_extension=$extension" -d xdebug.mode=coverage -d "auto_prepend_file=$root/tests/Support/BranchFilter.php" "$script" "${arguments[@]}" > "$report_dir/$label.stdout.json" 2> "$report_dir/$label.stderr.txt"; then
        printf '0' > "$report_dir/$label.exit"
    else
        printf '%s' "$?" > "$report_dir/$label.exit"
        status=1
    fi
    rmdir "$temp_dir"
    python3 "$root/tests/scripts/summarize-branches.py" "$report_dir/$label.raw.json" "$report_dir/$label.branches.json" > /dev/null
done <<'CONTEXTS'
construct-Base-Mcp|CP|7.5.0|construct:Base/Mcp
construct-Base-Tab|CP|7.5.0|construct:Base/Tab
construct-Base-Ft|CP|7.5.0|construct:Base/Ft
extension-menu|CP|7.5.0|extension-menu
bridge-ee2|PAGE|2.5.0|bridge-version
bridge-ee3|PAGE|3.5.0|bridge-version
bridge-ee4|PAGE|4.3.0|bridge-version
bridge-ee5|PAGE|5.5.0|bridge-version
bridge-ee6|PAGE|6.5.0|bridge-version
bridge-ee7|PAGE|7.5.0|bridge-version
archive-index|PAGE|7.5.0|archive-index
updater-ee2|PAGE|2.5.0|updater-uninstall
updater-ee5|PAGE|5.5.0|updater-uninstall
updater-ee6|PAGE|6.5.0|updater-uninstall
extract-checksum|PAGE|7.5.0|extract:checksum
extract-size|PAGE|7.5.0|extract:size
extract-truncated|PAGE|7.5.0|extract:truncated
loading-legacy-config|PAGE|2.5.0|legacy-config
loading-legacy-defined|PAGE|2.5.0|legacy-defined
loading-fieldtype-system|PAGE|7.5.0|fieldtype-system
loading-fieldtype-app|PAGE|7.5.0|fieldtype-app
loading-fieldtype-priority|PAGE|7.5.0|fieldtype-priority
loading-fieldtype-defined-version|PAGE|7.5.0|fieldtype-defined-version
loading-fieldtype-installer|PAGE|7.5.0|fieldtype-installer
loading-fieldtype-installer-ancestor|PAGE|7.5.0|fieldtype-installer-ancestor
loading-fieldtype-predeclared|PAGE|7.5.0|fieldtype-predeclared
CONTEXTS
python3 "$root/tests/scripts/render-native-coverage.py" "$report_dir" "$report_dir/summary.md"
exit "$status"
