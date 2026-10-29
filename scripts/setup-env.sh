#!/usr/bin/env bash
set -euo pipefail

project_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$project_dir"

if [[ -e .env ]]; then
    echo "ERROR: .env ya existe; no se sobrescribirá." >&2
    exit 1
fi

mapfile -t backups < <(find data -maxdepth 1 -type f -name '*.bak' -printf '%f\n' | sort)
if [[ ${#backups[@]} -ne 1 ]]; then
    echo "ERROR: se esperaba exactamente un respaldo .bak en data/; encontrados: ${#backups[@]}." >&2
    exit 1
fi

sa_password="Aa1!$(openssl rand -hex 24)"
app_password="Bb2!$(openssl rand -hex 24)"

umask 077
temp_env="$(mktemp .env.tmp.XXXXXX)"
trap 'rm -f "$temp_env"' EXIT

cat >"$temp_env" <<EOF
APP_ENV=local
APP_BASE_URL=http://127.0.0.1:8080/
APP_DEBUG=0
APP_DB_HOST=sqlserver
APP_DB_PORT=1433
APP_DB_NAME=alto_tabancura_local
APP_DB_USER=altotabancura_app
APP_DB_PASSWORD=${app_password}
APP_OUTPUT_DIR=../generated/

ACCEPT_EULA=Y
MSSQL_PID=Developer
MSSQL_SA_PASSWORD=${sa_password}
BACKUP_FILE=${backups[0]}
EOF

mv "$temp_env" .env
trap - EXIT
echo "Creado .env local con credenciales aleatorias. El archivo está excluido de Git."
