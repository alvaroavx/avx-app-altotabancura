#!/usr/bin/env bash
set -euo pipefail

project_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$project_dir"

set -a
source .env
set +a

admin_sqlcmd=(docker compose exec -T -e "SQLCMDPASSWORD=$MSSQL_SA_PASSWORD" sqlserver \
    /opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -No -b)
app_sqlcmd=(docker compose exec -T -e "SQLCMDPASSWORD=$APP_DB_PASSWORD" sqlserver \
    /opt/mssql-tools18/bin/sqlcmd -S localhost -U "$APP_DB_USER" -d "$APP_DB_NAME" -No -b)

cat <<EOF | "${admin_sqlcmd[@]}" -W -s '|'
SET NOCOUNT ON;
SELECT name, state_desc, compatibility_level
FROM sys.databases
WHERE name = N'${APP_DB_NAME}';
USE [${APP_DB_NAME}];
SELECT
    (SELECT COUNT(*) FROM sys.tables) AS tables_count,
    (SELECT COUNT(*) FROM sys.views) AS views_count,
    (SELECT COUNT(*) FROM sys.procedures) AS procedures_count,
    (SELECT COUNT(*) FROM sys.objects WHERE type IN ('FN','IF','TF')) AS functions_count;
GO
EOF

echo "== Conexión con la cuenta de aplicación =="
printf "SET NOCOUNT ON; SELECT DB_NAME() AS database_name, SUSER_SNAME() AS login_name;\nGO\n" \
    | "${app_sqlcmd[@]}" -W -s '|'
