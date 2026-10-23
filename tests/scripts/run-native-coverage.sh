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
    flags=(-d "opcache.enable_cli=0")
    if [[ "$label" == autoload-* ]]; then
        script="$root/tests/scripts/autoload-probe.php"
        arguments+=("$temp_dir")
        flags+=(-d "opcache.enable_cli=1" -d "opcache.validate_permission=0")
    fi
    if [[ "$case_name" == opcache ]]; then
        flags+=(-d "opcache.validate_permission=1")
    fi
    if [[ "$case_name" == no-opcache-api ]]; then
        flags+=(-d "disable_functions=opcache_get_configuration")
    fi
    if [[ "$case_name" == restricted-opcache-api ]]; then
        flags+=(-d "opcache.restrict_api=$temp_dir")
    fi
    if [[ "$label" == pagination-* ]]; then
        script="$root/tests/scripts/pagination-probe.php"
    fi
    if [[ "$label" == session-* ]]; then
        script="$root/tests/scripts/session-probe.php"
        arguments+=("$temp_dir")
        flags+=(-d "session.save_handler=files" -d "session.use_cookies=0" -d "session.cache_limiter=")
    fi
    if LOW_TITLE_COVERAGE_SOURCE= LOW_TITLE_EE_REQUEST="$request" LOW_TITLE_EE_VERSION="$ee_version" LOW_TITLE_BRANCH_REPORT="$report_dir/$label.raw.json" TMPDIR="$temp_dir" "$runtime" -d error_reporting=-1 -d display_errors=stderr -d "sys_temp_dir=$temp_dir" -d "zend_extension=$extension" -d xdebug.mode=coverage -d "auto_prepend_file=$root/tests/Support/BranchFilter.php" "${flags[@]}" "$script" "${arguments[@]}" > "$report_dir/$label.stdout.json" 2> "$report_dir/$label.stderr.txt"; then
        printf '0' > "$report_dir/$label.exit"
    else
        printf '%s' "$?" > "$report_dir/$label.exit"
        status=1
    fi
    python3 - "$temp_dir" <<'CLEANUP'
import pathlib
import shutil
import sys

owned = pathlib.Path(sys.argv[1])
shutil.rmtree(owned)
assert not owned.exists()
CLEANUP
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
autoload-unrelated|PAGE|7.5.0|unrelated
autoload-sibling-namespace|PAGE|7.5.0|sibling-namespace
autoload-missing-archive|PAGE|7.5.0|missing-archive
autoload-missing-class|PAGE|7.5.0|missing-class
autoload-native|PAGE|7.5.0|native
autoload-opcache|PAGE|7.5.0|opcache
autoload-extraction|PAGE|7.5.0|extraction
autoload-no-opcache-api|PAGE|7.5.0|no-opcache-api
autoload-restricted-opcache-api|PAGE|7.5.0|restricted-opcache-api
pagination-named-default|PAGE|7.5.0|named-default
pagination-global-explicit|PAGE|7.5.0|global-explicit
pagination-global-default|PAGE|7.5.0|global-default
session-plain|PAGE|7.5.0|plain
session-depth|PAGE|7.5.0|depth
session-mode|PAGE|7.5.0|mode
session-tmp-file|PAGE|7.5.0|tmp-file
session-tmp-readonly|PAGE|7.5.0|tmp-readonly
session-missing|PAGE|7.5.0|missing
session-file|PAGE|7.5.0|file
session-empty|PAGE|7.5.0|empty
CONTEXTS
python3 "$root/tests/scripts/render-native-coverage.py" "$report_dir" "$report_dir/summary.md"
exit "$status"
