#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")"
name="${1:-phpunit-9.phar}"
checksum="$(awk -v name="$name" '$1 == name { print $3 }' versions.txt)"
if [[ -z "$checksum" || ! -f "$name" ]]; then
    printf 'Missing pinned PHPUnit tool: %s\n' "$name" >&2
    exit 1
fi
printf '%s  %s\n' "$checksum" "$name" | shasum -a 256 -c - >/dev/null
