# Ambiente local con Docker — Alto Tabancura

## Propósito y límites

Este laboratorio ejecuta la aplicación original sobre PHP 8.2 y una copia local de SQL Server. Su objetivo es reproducir y corregir la generación y descarga de certificados sin conectar con producción.

No es una arquitectura objetivo de producción. No despliega, no sincroniza datos, no publica puertos de SQL Server y no habilita integraciones externas.

## Componentes

- `web`: imagen oficial PHP 8.2 con Apache, Microsoft ODBC Driver 18 y `sqlsrv`/`pdo_sqlsrv` 5.12.
- `sqlserver`: imagen oficial SQL Server 2019 Developer `2019-CU32-GDR12-ubuntu-20.04`.
- `mssql-data`: volumen persistente de la base local.
- `generated-files`: volumen de documentos generados en `/var/www/generated`, fuera del document root.
- Red interna exclusiva para PHP → SQL Server; SQL Server no participa en la red frontal.
- Red frontal de Apache necesaria para publicar el servicio únicamente en localhost. Las integraciones externas permanecen deshabilitadas por configuración local.
- Único puerto publicado: `127.0.0.1:8080` para Apache.

Se eligió SQL Server 2019 por ser la versión Linux viable más cercana a SQL Server 2016. La restauración conserva el nivel de compatibilidad 130.

## Requisitos del equipo

- Docker Engine y Docker Compose v2.
- Al menos 2 GB de memoria disponibles para SQL Server; se recomienda más para trabajar con comodidad.
- Un único archivo `.bak` dentro de `data/`.
- El usuario local debe poder ejecutar Docker.

En Ubuntu:

```bash
sudo apt-get install -y docker.io docker-compose-v2
sudo systemctl enable --now docker
sudo usermod -aG docker "$USER"
```

Después de agregar el grupo, cierre sesión y vuelva a entrar, o ejecute `newgrp docker` en el terminal actual.

## Preparación inicial

Desde la raíz del repositorio:

```bash
./scripts/setup-env.sh
docker compose config
docker compose build web
docker compose up -d sqlserver
docker compose ps
```

`setup-env.sh` crea `.env` con contraseñas locales aleatorias. Se niega a sobrescribir un `.env` existente y requiere exactamente un `.bak`. Nunca copie esas contraseñas a documentos, commits o tickets.

## Inspección y restauración

Espere hasta que `sqlserver` figure como `healthy` y ejecute:

```bash
./scripts/inspect-backup.sh
./scripts/restore-db.sh
./scripts/verify-db.sh
```

La inspección ejecuta `RESTORE VERIFYONLY`, `RESTORE HEADERONLY` y `RESTORE FILELISTONLY`. La restauración obtiene los nombres lógicos del respaldo, usa rutas Linux con `WITH MOVE`, fija compatibilidad 130 y crea una cuenta local con permiso de conexión y ejecución de procedimientos.

`restore-db.sh` se detiene si la base ya existe. No incluye una opción de reemplazo silencioso.

## Levantar y comprobar la aplicación

### Preparar cuentas ficticias

Para crear una cuenta administrativa local y tres alumnos ficticios con historiales de una, dos y tres vacunas:

```bash
./scripts/prepare-test-accounts.sh
```

El script se niega a ejecutarse fuera de `APP_ENV=local`. Las cuentas se crean solamente en la base restaurada dentro de Docker; no reutilizan contraseñas de producción ni exponen identidades de alumnos reales.

### Ejecutar la suite automatizada

```bash
./tests/run.sh
```

La suite comprueba servicios y extensiones, portada, login inválido, login de alumnos con una, dos y tres vacunas, contenido de la ficha, generación y registro del certificado, descarga HTTP y contenido del PDF. Los registros de certificados creados durante la ejecución se eliminan al finalizar; las cuentas ficticias se conservan para pruebas manuales.

```bash
docker compose up -d web
./scripts/smoke-test.sh
```

Abra [http://127.0.0.1:8080](http://127.0.0.1:8080). Las pruebas funcionales deben cubrir cuentas autorizadas de Alumno, Docente y Administrador, evitando acciones masivas.

Secuencia mínima:

1. Cargar portada y formulario de autenticación.
2. Confirmar conexión PHP → SQL Server.
3. Iniciar sesión con cada perfil autorizado.
4. Consultar vacunación y certificados existentes.
5. Generar un certificado nuevo con un registro de laboratorio controlado.
6. Descargar el PDF y revisar nombre, RUT, tildes, eñes, vacunas, lotes, firma, timbre y código.
7. Confirmar la persistencia esperada sin enviar correos ni invocar integraciones externas.

## Operación cotidiana

Levantar:

```bash
docker compose up -d
```

Ver estado y logs:

```bash
docker compose ps
docker compose logs --tail=200 web
docker compose logs --tail=200 sqlserver
```

Detener sin borrar datos:

```bash
docker compose stop
```

Detener y retirar contenedores sin borrar volúmenes:

```bash
docker compose down
```

## Reconstrucción deliberada de la base local

El siguiente comando elimina la base restaurada y todos los documentos generados del laboratorio:

```bash
docker compose down -v
```

Es una operación destructiva local. Ejecútela solamente después de confirmar que no necesita conservar pruebas. Luego repita la inspección y restauración.

## Diagnóstico

Versiones y extensiones PHP:

```bash
docker compose exec web php -v
docker compose exec web php -m | grep -E 'sqlsrv|pdo_sqlsrv|gd|mbstring|curl|openssl'
```

Conectividad interna y estado SQL:

```bash
./scripts/verify-db.sh
docker compose exec web php -r 'var_export(sqlsrv_client_info(sqlsrv_connect(getenv("APP_DB_HOST").",".getenv("APP_DB_PORT"), ["UID"=>getenv("APP_DB_USER"), "PWD"=>getenv("APP_DB_PASSWORD"), "Database"=>getenv("APP_DB_NAME"), "TrustServerCertificate"=>true])));'
```

No pegue salidas que contengan datos clínicos o credenciales en reportes.

## Diferencias respecto de producción

| Área | Laboratorio | Producción conocida |
|---|---|---|
| Servidor web | Apache sobre Linux | IIS sobre Windows Server |
| PHP | 8.2 en contenedor | 8.2.x en hosting |
| SQL Server | 2019 Developer, compatibilidad 130 | 2016 SP3 |
| Rutas | Sensibles a mayúsculas; `/` | Windows y rutas de IIS |
| Base URL | `127.0.0.1:8080` | Dominio productivo |
| Credenciales | Aleatorias, solo locales | Administradas fuera de este laboratorio |
| Correo e integraciones | Aislados por configuración y red interna | No verificados en esta etapa |
| Archivos PDF | Volumen fuera del document root | Ruta productiva por confirmar |

## Evidencia de esta ejecución

Estado inicial antes de Docker:

- El equipo dispone de 30 GiB de RAM y espacio suficiente para el laboratorio.
- El archivo recibido fue reconocido como respaldo de Microsoft SQL Server.
- El hotfix de FPDF generó un PDF ficticio válido bajo PHP moderno.
- La portada pudo renderizarse con la configuración local aislada.

Pendiente de completar con evidencia real:

- Construcción de imágenes y estado de contenedores.
- `RESTORE VERIFYONLY`, encabezado, lista de archivos y restauración.
- Inventario de tablas, vistas, funciones y procedimientos.
- Conexión desde PHP con la cuenta local.
- Autenticación por perfiles y flujos con datos.
- Generación, persistencia y descarga de un certificado real de laboratorio.

No se debe declarar recuperado el servicio mientras esos puntos sigan pendientes.

## Archivos candidatos para el hotfix productivo

Esta lista debe cerrarse después de reproducir el fallo con la base local:

- `src/vendor/FPDF/doorlock.php`: carga de FPDF compatible con sensibilidad de mayúsculas en Linux.
- `src/vendor/FPDF/fpdf.php`: protección frente a funciones eliminadas desde PHP 7.
- `src/valk/alphonse.php`: entrega del PDF usando la ruta física resuelta.

Los cambios de Compose, Apache, scripts, rutas locales y variables de entorno pertenecen al laboratorio y no implican que deban desplegarse en SmarterASP.
