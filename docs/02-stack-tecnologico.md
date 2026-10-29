---
doc: stack-tecnologico
lectura_previa: 01-arquitectura.md
---

# 02 · Stack tecnológico y dependencias

## 1. Plataforma

| Capa | Tecnología | Versión | Evidencia |
|---|---|---|---|
| Lenguaje servidor | PHP | **8.5** | Producción observada en PHP 8.5.12; laboratorio fijado en `php:8.5.12-apache-bookworm` |
| Servidor web | IIS (producción) / Apache (soportado) | ❓ | `web.config` (IIS URL Rewrite) y `.htaccess`; detección en `valk/loader.php:3-11` |
| Base de datos | Microsoft SQL Server | ❓ | driver `sqlsrv_*` en `valk/mu/v1.0/SqlServer.php` |
| Driver PHP↔BD | `sqlsrv` (extensión de Microsoft) | **5.13.3 local** | `sqlsrv_connect`, `sqlsrv_query`; la versión 5.13 agrega soporte para PHP 8.5 |
| Hosting | SmarterASP.NET / site4now | 🔍 | host `sql7004.site4now.net`, prefijo de BD `DB_A42699_` |
| Protocolo | **HTTP sin TLS** | — | `manifest.php:14` define `http://` |
| Zona horaria | `America/Santiago` | — | `valk/constants.php:2` |

> PHP 8.5 es el runtime objetivo. El cambio corrige la obsolescencia del intérprete,
> pero no moderniza automáticamente las dependencias heredadas ni los controles de seguridad.
> Ver [`08-seguridad.md`](08-seguridad.md) y la decisión [D-01](decisiones/README.md).

### Adaptaciones aplicadas para PHP 8.5

La migración conserva los contratos de la aplicación y reemplaza incompatibilidades concretas:

| Problema heredado | Corrección |
|---|---|---|
| `utf8_encode()` / `utf8_decode()` deprecadas | Funciones explícitas `Latin1ToUtf8()` y `Utf8ToLatin1()` basadas en `mb_convert_encoding()` |
| FPDF 1.81 usa conversiones deprecadas | Conversión interna compatible y guardia para *magic quotes* inexistente |
| Acceso a strings con llaves en XLSXWriter | Acceso actualizado a corchetes y propiedades declaradas/inicializadas |
| Orden histórico de argumentos de `implode()` | Orden `separador, array` en UploadHandler y Google Resource |
| Autocargador Facebook corrupto | Autocargador PSR-4 válido; el módulo continúa desactivado |
| Microsoft `sqlsrv` 5.12 sin soporte para PHP 8.5 | Laboratorio actualizado a `sqlsrv`/`pdo_sqlsrv` 5.13.3 |

## 2. Dependencias de terceros (`vendor/`)

**No hay Composer ni `composer.json`.** Las librerías están copiadas manualmente,
con un archivo `doorlock.php` por librería que actúa como *autoloader* propio
(`valk/tools/tValk.php:16-23`).

| Librería | Uso real | Estado | Riesgo |
|---|---|---|---|
| **FPDF** | ✅ **En uso**: generación de todos los certificados PDF | 1.81, adaptada a PHP 8.5 | Dependencia heredada que debe mantenerse cubierta por pruebas de PDF |
| **PHPMailer** | ⚠️ Cargada pero **inoperante** (envío en bypass permanente) | `vendor/PHPMailer/` | ❗ versiones antiguas de PHPMailer tienen CVEs de RCE (CVE-2016-10033 y familia) — **verificar versión** |
| **Facebook SDK v5** | ❌ Desactivado (`Modulo_Facebook = 0`) | `vendor/Facebook/`; autocargador reparado | Código heredado sin uso funcional actual |
| **Google API Client** | ❌ Desactivado e **incompleto** (`autoload.php.old`, sin `doorlock.php`) | `vendor/Google/` | Código muerto; cargarlo fallaría |
| **Jodit** (editor WYSIWYG) | ❌ Desactivado (`Modulo_Jodit = 0`) | `vendor/Jodit/` | Código muerto |

### Librerías JavaScript (`valk/js/v1.0/`)

| Librería | Uso |
|---|---|
| jQuery (1.x, ~87 KB minificado) | ✅ Base de todo el cliente |
| jQuery Validate + additional-methods | ✅ Validación de formularios de login/registro |
| **jquery.base64.min** | ✅ Codificación del payload `rawdata` |
| **DataTables** | ✅ Tablas de resultados (alumnos, docentes, catálogos) |
| jquery.mousewheel | ✅ Cargado por `Scripts.php` |
| jquery.ui.widget + jquery.fileupload | ⚠️ Solo el *widget* se carga; `fileupload` está **comentado** en `Scripts.php:13` |
| Bootstrap (`bootstrap.min.js`, `bootstrap.css`) | ❌ Presente pero **comentado** en `Styles.php:10`, no se carga |
| jQuery.print | ❌ Presente, no referenciado |

> 🔍 Las versiones exactas de jQuery y DataTables no están declaradas en ningún
> archivo. jQuery 1.x tiene vulnerabilidades XSS conocidas (CVE-2015-9251, CVE-2020-11022/11023
> afectan a <3.5.0). ❓ **Requiere verificación manual del contenido de los archivos.**

## 3. Utilidades embebidas (`valk/mu/`)

| Archivo | Origen | Uso |
|---|---|---|
| `cUploadHandler.php` (1473 líneas) | blueimp/jQuery-File-Upload (`UploadHandler.php`) | ⚠️ Cargado por `valk/ro/rUploader.php` — **endpoint activo sin autenticación** (ver [SEC-02](08-seguridad.md)) |
| `cXLSXWriter.php` (962 líneas) | mk-j/PHP_XLSXWriter | Exportación a Excel vía `Excel()` en `valk/tools/tExcel.php` — ❌ **nunca invocada** desde el código de la app |
| `img/user/UploadHandler.php` | copia duplicada de blueimp | ❌ código muerto (`img/user/index.php` la instancia, pero esa ruta no está enlazada) |

## 4. Requisitos del entorno de ejecución

| Requisito | Motivo | Verificación |
|---|---|---|
| Extensión `sqlsrv` habilitada | acceso a BD | `SqlServer.php:38` |
| Extensión `openssl` habilitada | `encrypt_decrypt()` para `/e/` y `gate.php` | `tEncode.php:180-190` |
| Extensión `simplexml` habilitada | `ArrayToXml()` — usada para pasar filtros a los SP | `tFormato.php:157` |
| Extensión `mbstring` | conversiones explícitas entre ISO-8859-1 y UTF-8 | `Latin1ToUtf8()` / `Utf8ToLatin1()` |
| **Permiso de escritura** en `cache/` | generación de assets concatenados | `valk/scripts.php`, `valk/styles.php` |
| **Permiso de escritura** en `out/` | escritura de PDFs generados | `gate/omega/xCertificado.php:70` |
| **Permiso de escritura** en la raíz | `log.txt` escrito por `Log2()` | `valk/tools/tCore.php:38-42` |
| Variable `$_SERVER['APPL_PHYSICAL_PATH']` (IIS) o `CONTEXT_DOCUMENT_ROOT` (Apache) | resolución de rutas absolutas | `valk/loader.php:3-11` |
| Módulo de reescritura de URL | routing | `.htaccess` / `web.config` |

> ⚠️ **El directorio `out/` no existe en el repositorio.** Debe crearse manualmente
> con permisos de escritura o la generación de certificados falla silenciosamente
> (`FPDF::Output` con destino `'F'` sobre ruta inexistente).

## 5. Instalación y despliegue

❌ **No existe** proceso de despliegue documentado ni automatizado. Reconstruido por análisis:

```bash
# 1. Copiar el árbol completo al document root del servidor web
#    (FTP/SFTP — no hay build step)

# 2. Crear directorios de escritura ausentes
mkdir out static
chmod 775 out static cache
touch log.txt && chmod 664 log.txt

# 3. Ajustar manifest.php:
#    - $Manifest['Root'] y ['Steins'] → URL real del sitio
#    - $Manifest['Entorno']['DBServer'] → 'PRO' o 'LOCAL'
#    - $Manifest['Entorno']['Developer'] → 0 en producción, 1 en desarrollo

# 4. Ajustar gate/var.php:
#    - $Var['Prefix_Fisico'] → subdirectorio si la app NO está en la raíz
#      (actualmente vale '/AppCertificados', pensado para localhost)

# 5. Ajustar credenciales de BD en valk/mu/v1.0/Steins.php (¡hardcodeadas!)

# 6. Verificar que la BD tenga TODOS los procedimientos almacenados
#    listados en docs/03-modelo-datos.md  ← NO están en este repositorio

# 7. Al cambiar JS/CSS: borrar el contenido de cache/
```

> ⚠️ **Bloqueante para reconstruir el sistema desde cero**: el repositorio **no contiene
> el esquema de la base de datos ni los procedimientos almacenados**, donde reside
> toda la lógica de negocio. Sin un respaldo de la BD el sistema es irrecuperable.
> Ver [`09-mejoras-deuda-tecnica.md`](09-mejoras-deuda-tecnica.md) MEJ-02.

## 6. Entornos

| Entorno | `DBServer` | Host BD | Notas |
|---|---|---|---|
| **Producción** (`PRO`) | `PRO` | `sql7004.site4now.net` | Activo en `manifest.php:19` |
| **Local** (`LOCAL`) | `LOCAL` | `localhost` | Definido en `Steins.php:15-22`; usuario `usr_local` |
| Desarrollo (front) | — | — | `Entorno_Developer = 1`: sirve JS/CSS sin concatenar, muestra banner "MODO DEVELOPER" (`valk/kirito.php`), habilita `valk.debug.js` y la traza `Edward()` |

> ⚠️ Con `Entorno_Developer = 1`, la función `Edward()` (`valk/tools/tDebug.php`)
> **imprime todos los parámetros de cada petición en la consola del navegador**, y
> `DatosSesion()` (`valk/ro/rDebug.php`) vuelca `$_SESSION`, `$_COOKIE` y el manifiesto completo.

## 7. Observabilidad

| Aspecto | Estado |
|---|---|
| Log de aplicación | ⚠️ `Log2()` escribe texto plano a `log.txt` en la raíz web (**accesible por HTTP**). Última entrada: 2019-03-04. |
| Log de auditoría en BD | 🔍 Existe `spIns_Log_Log` / `spRec_Log_Log` vía `Morty.php`, pero **ninguna función de la app invoca a Morty** — la auditoría está muerta. |
| Métricas / APM | ❌ Ninguna |
| Health check | ⚠️ Solo `HeartBeat()` (`rLogin.php:321`): el cliente comprueba periódicamente si su sesión sigue viva |
| Trazas de error | ❌ `error_reporting(0)` en producción; `Watchdog::Execute()` retorna `1` sin hacer nada |
| Alertas | ❌ Ninguna |
