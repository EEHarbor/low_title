#!/usr/bin/env bash
set -euo pipefail
root="$(cd "$(dirname "$0")/../.." && pwd)"
runtime="${LOW_TITLE_PHP_BIN:-/Users/tomjaeger/Library/Application Support/Herd/bin/php84}"
"$root/tests/.phpunit/verify.sh" phpunit-9.phar
exec "$runtime" -d error_reporting=-1 -d display_errors=stderr "$root/tests/.phpunit/phpunit-9.phar" "$@"
