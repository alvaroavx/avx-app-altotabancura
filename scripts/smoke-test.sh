#!/usr/bin/env bash
set -euo pipefail

project_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$project_dir"

echo "== PHP y extensiones =="
docker compose exec -T web php -r '
printf("PHP %s\n", PHP_VERSION);
foreach (["sqlsrv", "pdo_sqlsrv", "gd", "mbstring", "curl", "openssl"] as $extension) {
    printf("%-12s %s\n", $extension, extension_loaded($extension) ? "OK" : "FALTA");
}
'

body_file="$(mktemp)"
trap 'rm -f "$body_file"' EXIT
status="$(curl --fail-with-body --silent --show-error --output "$body_file" --write-out '%{http_code}' http://127.0.0.1:8080/)"
[[ "$status" == "200" ]]
grep -qi '<html' "$body_file"
echo "HTTP /: 200 y documento HTML recibido."
