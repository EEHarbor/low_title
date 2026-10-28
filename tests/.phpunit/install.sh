#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")"
while read -r name version checksum; do
    if [[ ! -f "$name" ]]; then
        curl -fsSL "https://phar.phpunit.de/phpunit-$version.phar" -o "$name"
    fi
    printf '%s  %s\n' "$checksum" "$name" | shasum -a 256 -c -
done < versions.txt
