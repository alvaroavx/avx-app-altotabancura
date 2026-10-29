#!/usr/bin/env bash
set -euo pipefail

project_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$project_dir"

set -a
source .env
set +a

base_url="${APP_BASE_URL%/}"
admin_password="${TEST_ADMIN_PASSWORD:-AltoLocal2026!}"
student_password="${TEST_STUDENT_PASSWORD:-9999}"
tmp_dir="$(mktemp -d)"
tests_passed=0
certificate_baseline=""

admin_sqlcmd() {
    docker compose exec -T \
        -e "SQLCMDPASSWORD=$MSSQL_SA_PASSWORD" \
        sqlserver /opt/mssql-tools18/bin/sqlcmd \
        -S localhost -U sa -No -b -d "$APP_DB_NAME" "$@"
}

sql_scalar() {
    local query="$1"
    admin_sqlcmd -h -1 -W -Q "SET NOCOUNT ON; $query" \
        | tr -d '\r' \
        | sed '/^[[:space:]]*$/d' \
        | tail -n 1 \
        | xargs
}

cleanup() {
    if [[ "$certificate_baseline" =~ ^[0-9]+$ ]]; then
        admin_sqlcmd -Q "SET NOCOUNT ON;
            DELETE c
            FROM dbo.Certificado c
            JOIN dbo.Usuario u ON u.IdUsuario = c.IdUsuario
            WHERE c.IdCertificado > $certificate_baseline
              AND u.Username IN ('99999991','99999992','99999993');" >/dev/null 2>&1 || true
    fi
    rm -rf "$tmp_dir"
}
trap cleanup EXIT

pass() {
    tests_passed=$((tests_passed + 1))
    printf 'ok %02d - %s\n' "$tests_passed" "$1"
}

fail() {
    printf 'not ok - %s\n' "$1" >&2
    exit 1
}

assert_eq() {
    local expected="$1"
    local actual="$2"
    local description="$3"
    [[ "$actual" == "$expected" ]] || fail "$description (esperado: $expected; recibido: $actual)"
    pass "$description"
}

assert_contains() {
    local file="$1"
    local text="$2"
    local description="$3"
    grep -Fq "$text" "$file" || fail "$description"
    pass "$description"
}

assert_not_contains() {
    local file="$1"
    local text="$2"
    local description="$3"
    ! grep -Fq "$text" "$file" || fail "$description"
    pass "$description"
}

make_payload() {
    local action="$1"
    shift
    local raw="<njong>valk<alvax>${action}"

    while (( $# >= 2 )); do
        raw+="<njong>$1<alvax>$2"
        shift 2
    done

    raw+="<njong>edward<alvax>1<njong>load<alvax>dashboard<njong>idload<alvax>1<njong>media<alvax>1280"
    RAW_PAYLOAD="$raw" php -r 'echo base64_encode(rawurlencode(getenv("RAW_PAYLOAD")));'
}

post_action() {
    local cookie_jar="$1"
    local response_file="$2"
    local action="$3"
    shift 3
    local payload
    local status

    payload="$(make_payload "$action" "$@")"
    status="$(curl --silent --show-error \
        --cookie "$cookie_jar" --cookie-jar "$cookie_jar" \
        --output "$response_file" --write-out '%{http_code}' \
        --data-urlencode "rawdata=$payload" \
        "$base_url/g")"
    [[ "$status" == "200" ]] || fail "$action responde HTTP $status"
}

for command in docker curl php pdfinfo pdftotext; do
    command -v "$command" >/dev/null || fail "Falta el comando requerido: $command"
done
pass "herramientas de prueba disponibles"

[[ "${APP_ENV:-}" == "local" ]] || fail "La suite solo puede ejecutarse con APP_ENV=local"
[[ "$base_url" == "http://127.0.0.1:8080" ]] || fail "APP_BASE_URL debe apuntar al laboratorio local"
pass "entorno limitado a localhost"

running_services="$(docker compose ps --status running --services)"
grep -qx 'sqlserver' <<<"$running_services" || fail "SQL Server no está ejecutándose"
grep -qx 'web' <<<"$running_services" || fail "El servidor web no está ejecutándose"
pass "servicios Docker en ejecución"

missing_extensions="$(docker compose exec -T web php -r '
$required = ["sqlsrv", "pdo_sqlsrv", "gd", "mbstring", "curl", "openssl", "zip"];
echo implode(",", array_values(array_filter($required, fn($name) => !extension_loaded($name))));
')"
assert_eq "" "$missing_extensions" "PHP tiene todas las extensiones requeridas"

docker compose exec -T -w /var/www/html web php < tests/php85-compat.php
pass "runtime, codificación, PDF y XLSX son compatibles con PHP 8.5"

./scripts/prepare-test-accounts.sh >/dev/null
pass "fixtures ficticios preparados"

home_body="$tmp_dir/home.html"
home_status="$(curl --silent --show-error --output "$home_body" --write-out '%{http_code}' "$base_url/")"
assert_eq "200" "$home_status" "portada responde HTTP 200"
assert_contains "$home_body" "<html lang=\"es\">" "portada entrega la carcasa HTML"
assert_contains "$home_body" 'name="mobile-web-app-capable" content="yes"' "portada incluye la metaetiqueta móvil vigente"

guest_cookie="$tmp_dir/guest.cookies"
guest_body="$tmp_dir/guest-dashboard.html"
post_action "$guest_cookie" "$guest_body" LoadMidBlock init 1
assert_contains "$guest_body" "Plataforma de Certificados" "portada AJAX contiene el formulario de acceso"

invalid_cookie="$tmp_dir/invalid.cookies"
invalid_body="$tmp_dir/invalid-login.html"
post_action "$invalid_cookie" "$invalid_body" Autentificar \
    usuario 99999991 pass clave-incorrecta redsocial 1
assert_contains "$invalid_body" "Usuario o contraseña incorrecta" "contraseña incorrecta es rechazada"
assert_not_contains "$invalid_body" "Warning:" "contraseña incorrecta no expone advertencias de PHP"

formatted_body="$tmp_dir/formatted-login.html"
post_action "$invalid_cookie" "$formatted_body" Autentificar \
    usuario 99.999.991-3 pass "$student_password" redsocial 1
assert_contains "$formatted_body" "Usuario o contraseña incorrecta" "RUT con formato es rechazado según el contrato de acceso"
assert_not_contains "$formatted_body" "Warning:" "usuario inexistente no expone advertencias de PHP"

certificate_baseline="$(sql_scalar 'SELECT ISNULL(MAX(IdCertificado),0) FROM dbo.Certificado;')"
[[ "$certificate_baseline" =~ ^[0-9]+$ ]] || fail "No se pudo obtener el estado inicial de certificados"

student_users=(99999991 99999992 99999993)
student_names=("Alumno Prueba Uno" "Alumno Prueba Dos" "Alumno Prueba Tres")
student_ruts=("99.999.991-3" "99.999.992-1" "99.999.993-K")
student_vaccine_counts=(1 2 3)

for index in "${!student_users[@]}"; do
    username="${student_users[$index]}"
    expected_name="${student_names[$index]}"
    expected_rut="${student_ruts[$index]}"
    expected_vaccines="${student_vaccine_counts[$index]}"
    cookie_jar="$tmp_dir/${username}.cookies"
    login_body="$tmp_dir/${username}-login.html"
    dashboard_body="$tmp_dir/${username}-dashboard.html"
    generate_body="$tmp_dir/${username}-generate.txt"
    pdf_file="$tmp_dir/${username}.pdf"
    pdf_headers="$tmp_dir/${username}-headers.txt"
    pdf_text="$tmp_dir/${username}.txt"

    user_id="$(sql_scalar "SELECT IdUsuario FROM dbo.Usuario WHERE Username='$username';")"
    [[ "$user_id" =~ ^[0-9]+$ ]] || fail "No existe el fixture $username"

    vaccine_count="$(sql_scalar "SELECT COUNT(*) FROM dbo.UsuarioVacuna WHERE IdUsuario=$user_id;")"
    assert_eq "$expected_vaccines" "$vaccine_count" "$username conserva $expected_vaccines vacuna(s)"

    post_action "$cookie_jar" "$login_body" Autentificar \
        usuario "$username" pass "$student_password" redsocial 1
    assert_contains "$login_body" '$.LoadMidBlock(2)' "$username inicia sesión"

    post_action "$cookie_jar" "$dashboard_body" LoadMidBlock init 2
    assert_contains "$dashboard_body" "$expected_name" "$username visualiza su identidad ficticia"
    assert_contains "$dashboard_body" "$expected_rut" "$username visualiza su RUT ficticio"
    assert_contains "$dashboard_body" "Descargar certificado" "$username dispone de descarga de certificado"

    certificates_before="$(sql_scalar "SELECT COUNT(*) FROM dbo.Certificado WHERE IdUsuario=$user_id;")"
    post_action "$cookie_jar" "$generate_body" GenerarCertificado \
        idusuario "$user_id" multiple 0

    expected_url="$base_url/o/certificado${user_id}.pdf"
    assert_eq "$expected_url" "$(cat "$generate_body")" "$username genera una URL local de certificado"

    certificates_after="$(sql_scalar "SELECT COUNT(*) FROM dbo.Certificado WHERE IdUsuario=$user_id;")"
    assert_eq "$((certificates_before + 1))" "$certificates_after" "$username registra la emisión en SQL Server"

    download_status="$(curl --silent --show-error \
        --dump-header "$pdf_headers" --output "$pdf_file" --write-out '%{http_code}' \
        "$expected_url")"
    assert_eq "200" "$download_status" "$username descarga el certificado"
    grep -Eiq '^Content-Type:[[:space:]]*application/pdf' "$pdf_headers" \
        || fail "$username recibe Content-Type application/pdf"
    pass "$username recibe Content-Type application/pdf"

    [[ "$(head -c 5 "$pdf_file")" == '%PDF-' ]] || fail "$username recibe un archivo con firma PDF"
    pass "$username recibe un archivo con firma PDF"

    pdf_size="$(wc -c < "$pdf_file" | xargs)"
    (( pdf_size > 10000 )) || fail "$username recibe un PDF no vacío"
    pass "$username recibe un PDF no vacío"

    pdf_pages="$(pdfinfo "$pdf_file" | awk '/^Pages:/ {print $2}')"
    assert_eq "1" "$pdf_pages" "$username recibe un certificado de una página"

    pdftotext "$pdf_file" "$pdf_text"
    assert_contains "$pdf_text" "Certificado de Vacunación" "$username recibe el documento esperado"
    assert_contains "$pdf_text" "${expected_name#Alumno }" "$username aparece en el certificado"
    assert_contains "$pdf_text" "$expected_rut" "$username conserva el RUT correcto en el certificado"
    assert_contains "$pdf_text" "Código de validación:" "$username recibe código de validación"

    vaccine_dates="$(sql_scalar "SELECT STRING_AGG(CONVERT(varchar(10),Fecha,105),'|') WITHIN GROUP (ORDER BY Fecha,IdUsuarioVacuna) FROM dbo.UsuarioVacuna WHERE IdUsuario=$user_id;")"
    IFS='|' read -r -a dates <<<"$vaccine_dates"
    for vaccine_date in "${dates[@]}"; do
        assert_contains "$pdf_text" "$vaccine_date" "$username incluye la vacuna fechada $vaccine_date"
    done
done

admin_cookie="$tmp_dir/admin.cookies"
admin_login="$tmp_dir/admin-login.html"
admin_dashboard="$tmp_dir/admin-dashboard.html"
admin_content="$tmp_dir/admin-content.html"
post_action "$admin_cookie" "$admin_login" Autentificar \
    usuario alvaro@avx.cl pass "$admin_password" redsocial 1
assert_contains "$admin_login" '$.LoadMidBlock(2)' "administrador inicia sesión"
post_action "$admin_cookie" "$admin_dashboard" LoadMidBlock init 2
assert_contains "$admin_dashboard" '$.LoadAdmin()' "administrador carga el contenedor de su panel"
post_action "$admin_cookie" "$admin_content" LoadAdmin
assert_contains "$admin_content" "Nuevo Alumno" "administrador accede a los controles de su panel"

printf '\nResultado: %d comprobaciones aprobadas.\n' "$tests_passed"
