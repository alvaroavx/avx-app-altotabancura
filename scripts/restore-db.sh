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

for name in APP_DB_NAME APP_DB_USER; do
    value="${!name}"
    if [[ ! "$value" =~ ^[A-Za-z][A-Za-z0-9_]{2,63}$ ]]; then
        echo "ERROR: $name debe usar solo letras, números y guion bajo." >&2
        exit 1
    fi
done
if [[ ! "$BACKUP_FILE" =~ ^[A-Za-z0-9._-]+\.bak$ ]] || [[ ! -f "data/$BACKUP_FILE" ]]; then
    echo "ERROR: BACKUP_FILE no identifica un .bak válido dentro de data/." >&2
    exit 1
fi

sqlcmd=(docker compose exec -T -e "SQLCMDPASSWORD=$MSSQL_SA_PASSWORD" sqlserver \
    /opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -No -b)
backup_path="/var/opt/mssql/backup/$BACKUP_FILE"

existing="$({ printf "SET NOCOUNT ON; SELECT COUNT(*) FROM sys.databases WHERE name = N'%s';\nGO\n" "$APP_DB_NAME"; } | "${sqlcmd[@]}" -h -1 -W | tr -d '[:space:]')"
if [[ "$existing" != "0" ]]; then
    echo "ERROR: la base local $APP_DB_NAME ya existe. No se sobrescribirá." >&2
    echo "Para reconstruir desde cero se requiere aprobar y eliminar explícitamente el volumen local." >&2
    exit 2
fi

echo "Verificando integridad lógica del respaldo..."
printf "RESTORE VERIFYONLY FROM DISK = N'%s';\nGO\n" "$backup_path" | "${sqlcmd[@]}"

filelist="$(mktemp)"
restore_sql="$(mktemp)"
trap 'rm -f "$filelist" "$restore_sql"' EXIT
printf "RESTORE FILELISTONLY FROM DISK = N'%s';\nGO\n" "$backup_path" \
    | "${sqlcmd[@]}" -h -1 -W -s '|' >"$filelist"

moves=""
data_index=0
log_index=0
while IFS='|' read -r logical_name physical_name file_type rest; do
    logical_name="$(printf '%s' "$logical_name" | xargs)"
    file_type="$(printf '%s' "$file_type" | xargs)"
    [[ "$file_type" == "D" || "$file_type" == "L" ]] || continue
    [[ -n "$logical_name" ]] || continue

    escaped_logical="$(printf '%s' "$logical_name" | sed "s/'/''/g")"
    if [[ "$file_type" == "D" ]]; then
        data_index=$((data_index + 1))
        extension="ndf"
        [[ $data_index -eq 1 ]] && extension="mdf"
        target="/var/opt/mssql/data/${APP_DB_NAME}_data_${data_index}.${extension}"
    else
        log_index=$((log_index + 1))
        target="/var/opt/mssql/data/${APP_DB_NAME}_log_${log_index}.ldf"
    fi
    moves+="    MOVE N'${escaped_logical}' TO N'${target}',"$'\n'
done <"$filelist"

if [[ $data_index -eq 0 || $log_index -eq 0 ]]; then
    echo "ERROR: no fue posible identificar archivos lógicos de datos y log." >&2
    exit 1
fi

app_password_sql="$(printf '%s' "$APP_DB_PASSWORD" | sed "s/'/''/g")"
cat >"$restore_sql" <<EOF
RESTORE DATABASE [${APP_DB_NAME}]
FROM DISK = N'${backup_path}'
WITH
${moves}    RECOVERY,
    STATS = 5;
GO
ALTER DATABASE [${APP_DB_NAME}] SET COMPATIBILITY_LEVEL = 130;
GO
IF NOT EXISTS (SELECT 1 FROM sys.server_principals WHERE name = N'${APP_DB_USER}')
    CREATE LOGIN [${APP_DB_USER}] WITH PASSWORD = N'${app_password_sql}', CHECK_POLICY = OFF, CHECK_EXPIRATION = OFF;
ELSE
    ALTER LOGIN [${APP_DB_USER}] WITH PASSWORD = N'${app_password_sql}';
GO
USE [${APP_DB_NAME}];
IF NOT EXISTS (SELECT 1 FROM sys.database_principals WHERE name = N'${APP_DB_USER}')
    CREATE USER [${APP_DB_USER}] FOR LOGIN [${APP_DB_USER}];
GRANT CONNECT TO [${APP_DB_USER}];
GRANT EXECUTE TO [${APP_DB_USER}];
GO
EOF

echo "Restaurando $APP_DB_NAME sin reemplazar bases existentes..."
"${sqlcmd[@]}" -i /dev/stdin <"$restore_sql"
echo "Restauración terminada. Ejecuta scripts/verify-db.sh."
