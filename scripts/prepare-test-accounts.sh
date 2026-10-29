#!/usr/bin/env bash
set -euo pipefail

project_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$project_dir"

set -a
source .env
set +a

if [[ "${APP_ENV:-}" != "local" ]]; then
    echo "Este script solo puede ejecutarse con APP_ENV=local." >&2
    exit 1
fi

admin_password="${TEST_ADMIN_PASSWORD:-AltoLocal2026!}"
student_password="${TEST_STUDENT_PASSWORD:-9999}"

docker compose exec -T \
    -e "SQLCMDPASSWORD=$MSSQL_SA_PASSWORD" \
    -e "TEST_ADMIN_PASSWORD=$admin_password" \
    -e "TEST_STUDENT_PASSWORD=$student_password" \
    sqlserver /opt/mssql-tools18/bin/sqlcmd \
    -S localhost -U sa -No -b -d "$APP_DB_NAME" <<'SQL'
SET NOCOUNT ON;
SET XACT_ABORT ON;

DECLARE @AdminPassword varchar(200) = CONVERT(varchar(200), HASHBYTES('SHA1', '$(TEST_ADMIN_PASSWORD)'), 0);
DECLARE @StudentPassword varchar(200) = CONVERT(varchar(200), HASHBYTES('SHA1', '$(TEST_STUDENT_PASSWORD)'), 0);
DECLARE @AdminId int;
DECLARE @Source1 int;
DECLARE @Source2 int;
DECLARE @Source3 int;

SELECT TOP (1) @Source1 = u.IdUsuario
FROM dbo.Usuario u
JOIN dbo.UsuarioVacuna uv ON uv.IdUsuario = u.IdUsuario
WHERE u.IdTipoUsuario = 1 AND u.IdEstado = 1
GROUP BY u.IdUsuario
HAVING COUNT(*) = 1
ORDER BY u.IdUsuario;

SELECT TOP (1) @Source2 = u.IdUsuario
FROM dbo.Usuario u
JOIN dbo.UsuarioVacuna uv ON uv.IdUsuario = u.IdUsuario
WHERE u.IdTipoUsuario = 1 AND u.IdEstado = 1
GROUP BY u.IdUsuario
HAVING COUNT(*) = 2
ORDER BY u.IdUsuario;

SELECT TOP (1) @Source3 = u.IdUsuario
FROM dbo.Usuario u
JOIN dbo.UsuarioVacuna uv ON uv.IdUsuario = u.IdUsuario
WHERE u.IdTipoUsuario = 1 AND u.IdEstado = 1
GROUP BY u.IdUsuario
HAVING COUNT(*) = 3
ORDER BY u.IdUsuario;

IF @Source1 IS NULL OR @Source2 IS NULL OR @Source3 IS NULL
    THROW 51000, 'No hay historiales de referencia suficientes para preparar las cuentas ficticias.', 1;

BEGIN TRANSACTION;

SELECT @AdminId = IdUsuario
FROM dbo.Usuario
WHERE LOWER(Username) = 'alvaro@avx.cl';

IF @AdminId IS NULL
BEGIN
    INSERT dbo.Usuario
        (Nombres, Apellidos, Username, Password, IdSede, IdTipoUsuario, Rut, IdCarrera, FechaNacimiento, IdEstado)
    SELECT TOP (1)
        'Alvaro', 'Vargas', 'alvaro@avx.cl', @AdminPassword,
        IdSede, 3, '1-9', IdCarrera, '01-01-1990', 1
    FROM dbo.Usuario
    WHERE IdTipoUsuario = 3
    ORDER BY IdUsuario;

    SET @AdminId = SCOPE_IDENTITY();
END
ELSE
BEGIN
    UPDATE dbo.Usuario
    SET Nombres = 'Alvaro', Apellidos = 'Vargas', Password = @AdminPassword,
        IdTipoUsuario = 3, IdEstado = 1
    WHERE IdUsuario = @AdminId;
END;

DECLARE @TestUsers TABLE (
    Username varchar(50) NOT NULL,
    Nombres varchar(50) NOT NULL,
    Apellidos varchar(50) NOT NULL,
    Rut varchar(15) NOT NULL,
    SourceId int NOT NULL
);

INSERT @TestUsers (Username, Nombres, Apellidos, Rut, SourceId)
VALUES
    ('99999991', 'Alumno', 'Prueba Uno', '99.999.991-3', @Source1),
    ('99999992', 'Alumno', 'Prueba Dos', '99.999.992-1', @Source2),
    ('99999993', 'Alumno', 'Prueba Tres', '99.999.993-K', @Source3);

DECLARE @Username varchar(50);
DECLARE @Nombres varchar(50);
DECLARE @Apellidos varchar(50);
DECLARE @Rut varchar(15);
DECLARE @SourceId int;
DECLARE @TestId int;

DECLARE test_cursor CURSOR LOCAL FAST_FORWARD FOR
SELECT Username, Nombres, Apellidos, Rut, SourceId FROM @TestUsers ORDER BY Username;

OPEN test_cursor;
FETCH NEXT FROM test_cursor INTO @Username, @Nombres, @Apellidos, @Rut, @SourceId;

WHILE @@FETCH_STATUS = 0
BEGIN
    SET @TestId = NULL;
    SELECT TOP (1) @TestId = IdUsuario
    FROM dbo.Usuario
    WHERE Rut = @Rut
       OR Username = @Username
       OR Username = CONCAT('alumno.prueba', RIGHT(@Username, 1))
    ORDER BY CASE WHEN Rut = @Rut THEN 0 ELSE 1 END, IdUsuario;

    IF @TestId IS NULL
    BEGIN
        INSERT dbo.Usuario
            (Nombres, Apellidos, Username, Password, IdSede, IdTipoUsuario, Rut, IdCarrera, FechaNacimiento, IdEstado)
        SELECT
            @Nombres, @Apellidos, @Username, @StudentPassword,
            IdSede, 1, @Rut, IdCarrera, '01-01-2000', 1
        FROM dbo.Usuario
        WHERE IdUsuario = @SourceId;

        SET @TestId = SCOPE_IDENTITY();
    END
    ELSE
    BEGIN
        UPDATE dbo.Usuario
        SET Nombres = @Nombres, Apellidos = @Apellidos, Username = @Username,
            Password = @StudentPassword, Rut = @Rut, IdTipoUsuario = 1, IdEstado = 1
        WHERE IdUsuario = @TestId;

        DELETE dbo.UsuarioVacuna WHERE IdUsuario = @TestId;
    END;

    INSERT dbo.UsuarioVacuna (IdVacuna, IdUsuario, Fecha, Numero, Observacion)
    SELECT IdVacuna, @TestId, Fecha, Numero, Observacion
    FROM dbo.UsuarioVacuna
    WHERE IdUsuario = @SourceId;

    FETCH NEXT FROM test_cursor INTO @Username, @Nombres, @Apellidos, @Rut, @SourceId;
END;

CLOSE test_cursor;
DEALLOCATE test_cursor;

COMMIT TRANSACTION;

SELECT Username, IdTipoUsuario,
       (SELECT COUNT(*) FROM dbo.UsuarioVacuna uv WHERE uv.IdUsuario = u.IdUsuario) AS Vacunas
FROM dbo.Usuario u
WHERE Username IN ('alvaro@avx.cl', '99999991', '99999992', '99999993')
ORDER BY IdTipoUsuario DESC, Username;
GO
SQL

echo
echo "Cuentas locales preparadas."
echo "Contraseña de administrador: $admin_password"
echo "Contraseña de alumnos: $student_password"
