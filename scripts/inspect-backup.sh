#!/usr/bin/env bash
set -euo pipefail

project_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$project_dir"

if [[ ! -f .env ]]; then
    echo "ERROR: falta .env. Ejecuta scripts/setup-env.sh." >&2
    exit 1
fi

set -a
source .env
set +a

if [[ ! "$BACKUP_FILE" =~ ^[A-Za-z0-9._-]+\.bak$ ]] || [[ ! -f "data/$BACKUP_FILE" ]]; then
    echo "ERROR: BACKUP_FILE no identifica un .bak válido dentro de data/." >&2
    exit 1
fi

sqlcmd=(docker compose exec -T -e "SQLCMDPASSWORD=$MSSQL_SA_PASSWORD" sqlserver \
    /opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -No -b)
backup_path="/var/opt/mssql/backup/$BACKUP_FILE"

echo "== RESTORE VERIFYONLY =="
printf "RESTORE VERIFYONLY FROM DISK = N'%s';\nGO\n" "$backup_path" | "${sqlcmd[@]}"

echo "== RESTORE HEADERONLY =="
printf "RESTORE HEADERONLY FROM DISK = N'%s';\nGO\n" "$backup_path" | "${sqlcmd[@]}"

echo "== RESTORE FILELISTONLY =="
printf "RESTORE FILELISTONLY FROM DISK = N'%s';\nGO\n" "$backup_path" | "${sqlcmd[@]}"
