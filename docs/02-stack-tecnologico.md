---
doc: stack-tecnologico
lectura_previa: 01-arquitectura.md
---

# 02 · Stack tecnológico y dependencias

## 1. Plataforma

| Capa | Tecnología | Versión | Evidencia |
|---|---|---|---|
| Lenguaje servidor | PHP | **5.6** | `README.md` del repositorio ("Stein php 5.6"); sintaxis compatible (`array()` + `[]`, sin *type hints* escalares) |
| Servidor web | IIS (producción) / Apache (soportado) | ❓ | `web.config` (IIS URL Rewrite) y `.htaccess`; detección en `valk/loader.php:3-11` |
| Base de datos | Microsoft SQL Server | ❓ | driver `sqlsrv_*` en `valk/mu/v1.0/SqlServer.php` |
| Driver PHP↔BD | `sqlsrv` (extensión de Microsoft) | ❓ | `sqlsrv_connect`, `sqlsrv_query` |
| Hosting | SmarterASP.NET / site4now | 🔍 | host `sql7004.site4now.net`, prefijo de BD `DB_A42699_` |
| Protocolo | **HTTP sin TLS** | — | `manifest.php:14` define `http://` |
| Zona horaria | `America/Santiago` | — | `valk/constants.php:2` |

> ⚠️ **PHP 5.6 dejó de recibir soporte de seguridad el 31 de diciembre de 2018.**
> Cualquier vulnerabilidad del intérprete descubierta desde entonces está sin parchar.
> Ver [`09-mejoras-deuda-tecnica.md`](09-mejoras-deuda-tecnica.md) MEJ-01.

### Incompatibilidades conocidas con PHP ≥ 7.0

Si se intentara actualizar el intérprete, estos puntos **rompen**:

| Problema | Ubicación | Versión que rompe |
|---|---|---|
| `Clone` usado como nombre de método (palabra reservada) | `gate/class/cNeodoc.php:79` | PHP 7.0 (`clone` es reservada) — mitigado porque la clase no se carga |
| `utf8_encode()` / `utf8_decode()` deprecadas | ubicuo (`cUsuario.php`, `cCertificado.php`, `tEncode.php`) | PHP 8.2 (deprecation) |
| `constant()` sobre constante inexistente | `valk/ro/rUploader.php:7` | PHP 8.0 (pasa de *warning* a `Error`) |
| Acceso a índices de array inexistentes sin `isset` | `valk/gatekeeper.php:3-10`, `valk/ro/rUploader.php:3-10` | PHP 8.0 (*warning* → sigue funcionando) |
| `$this->$tag += ...` con propiedad dinámica | `gate/class/cCertificado.php:236` | PHP 8.2 (deprecation de propiedades dinámicas) |
| `each()`, `mssql_*` | comentados en `SqlServer.php:35` | ya eliminados en PHP 7 |

## 2. Dependencias de terceros (`vendor/`)

**No hay Composer ni `composer.json`.** Las librerías están copiadas manualmente,
con un archivo `doorlock.php` por librería que actúa como *autoloader* propio
(`valk/tools/tValk.php:16-23`).

| Librería | Uso real | Estado | Riesgo |
|---|---|---|---|
| **FPDF** | ✅ **En uso**: generación de todos los certificados PDF | `vendor/FPDF/fpdf.php` | Versión ❓ sin declarar |
| **PHPMailer** | ⚠️ Cargada pero **inoperante** (envío en bypass permanente) | `vendor/PHPMailer/` | ❗ versiones antiguas de PHPMailer tienen CVEs de RCE (CVE-2016-10033 y familia) — **verificar versión** |
| **Facebook SDK v5** | ❌ Desactivado (`Modulo_Facebook = 0`) | `vendor/Facebook/` | Código muerto; `doorlock.php` está **corrupto** (contiene bytes binarios tras la línea 60) |
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
| Extensión `mbstring`/`iconv` | ❌ **no requerida** (usa `utf8_encode/decode` nativas) | — |
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
