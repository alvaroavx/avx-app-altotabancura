---
doc: arquitectura-de-solucion
lectura_previa: 00-contexto-negocio.md
---

# 01 · Arquitectura de solución

## 1. Vista de contexto (C4 nivel 1)

```mermaid
graph TB
    subgraph Usuarios
        AL["👨‍🎓 Alumno"]
        DO["👩‍🏫 Docente"]
        AD["🛠️ Administrador"]
    end

    subgraph "Servidor Web (IIS o Apache)"
        APP["Aplicación PHP 5.6<br/>avx-app-altotabancura<br/><i>vacunatorioaltotabancura.cl</i>"]
        FS["📁 Sistema de archivos<br/>cache/ · out/ · static/"]
    end

    DB[("🗄️ SQL Server remoto<br/>sql7004.site4now.net<br/>DB_A42699_altotabancura")]
    SMTP["✉️ SMTP<br/>(configurado pero inoperante)"]
    GA["📊 Google Tag Manager"]
    FB["📊 Facebook Pixel"]

    AL & DO & AD -->|"HTTP (sin TLS)"| APP
    APP -->|"sqlsrv_query · exec spXxx"| DB
    APP -->|"escribe PDF/XLSX"| FS
    APP -.->|"PHPMailer (bypass activo)"| SMTP
    APP -.->|"solo si Plugin_GTag=1"| GA
    APP -.->|"solo si Plugin_FbPixel=1"| FB
```

> ⚠️ Los plugins de analítica están **configurados con IDs reales** (`gate/keys.php`) pero
> los flags `Plugin_GTag` / `Plugin_FbPixel` valen `'0'` por defecto (`valk/default/dManifest.php`)
> y `manifest.php` no los sobrescribe → **no se cargan actualmente**. ✅ Verificado en `valk/snitch.php:2,14`.

## 2. Vista de capas (C4 nivel 2)

```mermaid
graph TB
    subgraph CLIENTE["🌐 Navegador"]
        HTML["index.php<br/>cascarón HTML estático"]
        JQ["jQuery 1.x + plugins<br/>valk.core.js · valk.login.js"]
        JX["gate/js/jx*.js<br/>un archivo por módulo"]
    end

    subgraph ROUTER["🚦 Enrutamiento"]
        HT[".htaccess / web.config<br/>todo → index.php"]
        ALP["valk/alphonse.php<br/>Router + servidor de assets"]
    end

    subgraph DISPATCH["📮 Dispatcher"]
        GK["valk/gatekeeper.php<br/>call_user_func por nombre"]
    end

    subgraph PRESENTACION["🎨 Presentación (server-side)"]
        OM["gate/omega/x*.php<br/>funciones que hacen echo de HTML"]
        RO["valk/ro/r*.php<br/>rutinas globales del framework"]
        ALF["gate/alfa/a*.php<br/>solo si sesión admin"]
    end

    subgraph DOMINIO["🧩 Dominio"]
        CL["gate/class/c*.php<br/>Usuario · Vacuna · Sede …<br/><i>extends Wiss</i>"]
        CERT["Certificado<br/><i>extends FPDF</i>"]
    end

    subgraph SERVICIOS["⚙️ Servicios del framework (Valk)"]
        WISS["valk/Wiss.php<br/>fachada · traits w*"]
        VALK["valk/cValk.php<br/>orquestador: carga módulo y ejecuta"]
        MU["valk/mu/v1.0/*<br/>Login · Query · Mail · Zeus · Morty · Kirito"]
    end

    subgraph DATOS["🗄️ Acceso a datos"]
        ST["Steins.php<br/>credenciales por entorno"]
        QRY["Query.php<br/>selector de driver"]
        SQL["SqlServer.php<br/>sqlsrv_* + exec sp"]
    end

    DB[("SQL Server<br/>solo procedimientos<br/>almacenados")]

    HTML --> JQ --> JX
    JX -->|"POST /g<br/>rawdata=base64(...)"| HT
    HT --> ALP --> GK
    GK --> OM & RO & ALF
    OM --> CL --> WISS --> VALK --> MU
    MU --> ST --> QRY --> SQL --> DB
    OM --> CERT
    CERT -->|"escribe archivo"| OUT["📁 out/*.pdf"]
```

## 3. Decisiones arquitectónicas de fondo (y sus consecuencias)

| # | Decisión | Consecuencia positiva | Consecuencia negativa |
|---|---|---|---|
| DA-01 | **Un único endpoint HTTP** (`POST /g`) con dispatch por nombre de función | API mínima, cero configuración de rutas | Cualquier función global del proyecto es invocable por el cliente ⇒ superficie de ataque enorme (ver [SEC-01](08-seguridad.md)) |
| DA-02 | **El servidor devuelve fragmentos de HTML**, no JSON | Cliente trivial (`$(...).html(response)`), sin plantillas | Presentación y lógica mezcladas; imposible reutilizar la API; XSS por concatenación de strings |
| DA-03 | **Toda la lógica SQL en procedimientos almacenados** | Inmune a inyección SQL desde PHP (parámetros ligados en `SqlServer.php:27-30`); la BD es la fuente de verdad | La lógica de negocio es invisible en el repositorio; imposible versionar o revisar; ❓ no auditable sin acceso a la BD |
| DA-04 | **Framework propio (Valk) en lugar de uno estándar** | Control total, sin dependencias externas | Sin comunidad, sin parches de seguridad, sin documentación, imposible de contratar personal que lo conozca |
| DA-05 | **Sin gestor de dependencias** (librerías copiadas a `vendor/`) | Despliegue = copiar archivos | Versiones congeladas y desconocidas; no hay forma de aplicar parches de seguridad |
| DA-06 | **Concatenación de JS/CSS en caché generada en runtime** | Menos peticiones HTTP en producción | La app **escribe en su propio directorio** durante el request; caché invalidada solo manualmente |
| DA-07 | **Configuración por constantes PHP globales** (`Params()` + `define()`) | Acceso global sin inyección de dependencias | Estado global inmutable; imposible de testear; colisiones de nombres |
| DA-08 | **PDFs escritos a disco con nombre predecible y servidos por URL** | Descarga simple vía `<a href>` | Los PDFs con datos de salud quedan accesibles y enumerables (ver [SEC-03](08-seguridad.md)) |

## 4. Ciclo de vida de una petición

### 4.1 Carga inicial de la página (`GET /` o `GET /resultados/1`)

```mermaid
sequenceDiagram
    autonumber
    actor U as Navegador
    participant WS as IIS/Apache
    participant IX as index.php
    participant LD as valk/loader.php
    participant AL as valk/alphonse.php
    participant SC as valk/scripts.php<br/>valk/styles.php

    U->>WS: GET /resultados/1
    WS->>IX: reescritura (.htaccess / web.config)
    IX->>LD: include loader.php
    Note over LD: session_start()<br/>define(Root_Fisica, WebServer)<br/>carga constants.php, tools.php<br/>preloader.php → Params() define<br/>TODAS las constantes globales<br/>instancia $Wiss
    IX->>AL: require alphonse.php
    Note over AL: ¿es asset? → lo sirve y exit()<br/>¿es /g? → gatekeeper<br/>¿es /o/x? → archivo de salida<br/>si no: parsea shortcut →<br/>$Load='resultados', $IdLoad='1'
    AL-->>IX: (continúa)
    IX->>SC: emite <link> y <script> (usa caché)
    IX-->>U: HTML con <div id="constructor"<br/>data-load="resultados" data-idload="1">
    Note over U: body onload → $.LoadCore()<br/>→ primer POST /g
```

✅ Verificado en `index.php:1-30`, `valk/loader.php`, `valk/alphonse.php`.

> ⚠️ **Anomalía**: cuando `alphonse.php` no encuentra ruta ni asset, termina con
> `header("location:")` (cabecera `Location` **vacía**) en `valk/alphonse.php:172`.
> El header vacío es ignorado por los navegadores, por lo que el flujo continúa por accidente.

### 4.2 Cualquier interacción posterior (`POST /g`)

```mermaid
sequenceDiagram
    autonumber
    actor U as Navegador
    participant JS as valk.core.js
    participant AL as alphonse.php
    participant GK as gatekeeper.php
    participant FN as función PHP global
    participant CL as gate/class/c*.php
    participant W as Wiss → Valk → Steins
    participant DB as SQL Server

    U->>JS: click / evento
    JS->>JS: $.RawData("LoadResultadosPaginado", {...})
    Note over JS: serializa a "clave<alvax>valor<njong>…"<br/>→ encodeURIComponent → base64<br/>→ "rawdata=…"
    JS->>AL: POST /g (application/x-www-form-urlencoded)
    AL->>GK: incluye gatekeeper.php
    Note over GK: DecodePost(): base64_decode →<br/>urldecode → LimpiaHtml → utf8_decode<br/>→ split por <njong>/<alvax>
    GK->>GK: carga TODOS los r*.php y x*.php<br/>(a*.php solo si Sesion('admin'))
    GK->>FN: call_user_func($raw_data['valk'], $raw_data)
    FN->>CL: new Usuario()->GetPaginado(...)
    CL->>W: Wiss::Query('spSel_Usuario_Paginado', $Datos)
    W->>DB: exec spSel_Usuario_Paginado @p1=?, @p2=? …
    DB-->>W: filas
    W-->>CL: array asociativo
    CL-->>FN: array
    FN-->>U: echo de fragmento HTML (texto plano)
    U->>U: $("#resultsrow").html(response)
```

✅ Verificado en `valk/js/v1.0/valk.core.js:20-52`, `valk/gatekeeper.php`, `valk/tools/tEncode.php:85-98`.

> 🔍 **Nota sobre el "cifrado" del transporte**: `rawdata` **no está cifrado**, solo
> codificado en Base64 con un separador propietario. Es trivialmente legible y
> manipulable. Existe además una ruta alternativa cifrada con AES (`valk/gate.php`
> + `encrypt_decrypt()`), pero **no se usa**: el cliente siempre apunta a `/g`
> (`valk.core.js:5`), que resuelve a `gatekeeper.php`, no a `gate.php`.

## 5. Sistema de ruteo (Alphonse)

`valk/alphonse.php` es un router basado en expresiones regulares que resuelve, **en este orden**:

| Orden | Patrón de URL | Acción | Línea |
|---|---|---|---|
| 1 | `/test.php` | `include` directo del script de pruebas | `:28` |
| 2 | `/k/*.js`, `/k/*.css` | sirve desde `cache/` con `max-age=31536000` | `:32` |
| 3 | `/res/*.{png,jpg,svg,ico,gif,…}` | sirve recurso estático | `:37` |
| 4 | `/fonts/*` | ⚠️ regex incompleta (`(PENDIENTE)`), nunca coincide | `:43` |
| 5 | `/valk/{js,css}/*` | assets del framework (modo desarrollo) | `:48` |
| 6 | `/vendor/*.{js,css}` | assets de terceros | `:53` |
| 7 | `/gate/{js,css}/*` | assets de la app (modo desarrollo) | `:58` |
| 8 | `/static/*.{img}` | archivos subidos por usuarios | `:63` |
| 9 | `/o/<nombre>` | archivo generado en `out/` (PDF, XLSX) | `:68` |
| 10 | `/e/<payload-cifrado>` | endpoint de retorno OAuth (`valk/endpoint.php`) | `:79` |
| 11 | `/g` | **dispatcher** (`valk/gatekeeper.php`) | `:87` |
| 12 | `/u/*` | uploads — ⚠️ **bloque vacío, no implementado** | `:93` |
| 13 | cualquier otra | interpreta como *shortcut*: `/<vista>/<id>` | `:157-170` |

**Shortcuts registrados** (`gate/shortcut.php`): `dashboard`, `resultados`, `alumnos`,
`universidades`, `carreras`. Por defecto: `dashboard/1` (`gate/var.php:18-21`).

> ⚠️ El bucle que parsea shortcuts tiene un `break` incondicional al final de la primera
> iteración (`valk/alphonse.php:166`), por lo que **solo se evalúa el primer segmento** de la URL.

## 6. Sistema de configuración

La configuración se aplana a **constantes globales PHP** mediante `Params()`
(`valk/tools/tCore.php:8-21`), que recorre los arrays anidados y hace `define()`
concatenando las claves con `_`. Ejemplo: `$Manifest['Entorno']['Developer']` → constante `Entorno_Developer`.

**Orden de carga** (`valk/preloader.php`) — lo posterior **no** sobreescribe lo anterior,
porque `define()` ignora redefiniciones:

```mermaid
graph LR
    A["valk/default/dManifest.php<br/>dKeys · dVar · dMeta<br/><i>valores por defecto</i>"] --> B["valk/ro.php<br/><i>lista de rutinas</i>"]
    B --> C["manifest.php<br/><i>config del entorno</i>"]
    C --> D["gate/var.php<br/>gate/meta.php"]
    D --> E["gate/construct.php<br/><i>módulos a cargar</i>"]
    E --> F["gate/keys.php<br/><i>IDs de analítica</i>"]
    F --> G["gate/shortcut.php<br/><i>rutas amigables</i>"]
```

> ⚠️ **Trampa crítica de este diseño**: `Params($Manifest)` se ejecuta en `loader.php:22`
> **después** de cargar el preloader, y `define()` es de una sola vez. Como
> `valk/default/dKeys.php` ya define `Keys['OpenSSL']['Key']`, el archivo
> `valk/keys.php` (que contiene las mismas claves) **queda huérfano y sin efecto**.
> Las claves criptográficas efectivas son las del archivo *default*, versionadas en Git.

**Configuración efectiva en producción** (`manifest.php`):

| Constante | Valor | Efecto |
|---|---|---|
| `Gate` | `appcertificados` | prefijo de todas las claves de `$_SESSION` |
| `Root` / `Steins` | `http://vacunatorioaltotabancura.cl/` | URL base (⚠️ HTTP plano) |
| `Entorno_Developer` | `0` | usa JS/CSS concatenado de `cache/`; oculta banner de dev |
| `Entorno_DBServer` | `PRO` | credenciales de producción en `Steins.php` |
| `Entorno_Error` | `0` | `display_errors=0`, `error_reporting(0)` |
| `Entorno_BypassCorreo` | `1` | declarada pero **nunca leída** (el bypass real está hardcodeado) |
| `Version_Valk` | `1.0` | selecciona el directorio `valk/mu/v1.0/` |
| `Modulo_Facebook/Google/Jodit` | `0` | módulos desactivados |

## 7. Estrategia de caché de assets

En producción (`Entorno_Developer = 0`) la aplicación **genera archivos concatenados**
la primera vez que se solicita una página:

| Archivo generado | Contenido | Generado por |
|---|---|---|
| `cache/jv1.0.php` | lista de etiquetas `<script>` del framework | `valk/scripts.php:2-20` |
| `cache/jv1.0.js` | JS del framework concatenado y minimizado | `valk/mu/v1.0/Scripts.php` |
| `cache/jx1.0.js` | todos los `gate/js/jx*.js` concatenados | `valk/scripts.php:47-66` |
| `cache/ja1.0.js` | todos los `gate/js/ja*.js` (vacío: no existen) | `valk/scripts.php:68-87` |
| `cache/sv1.0.php` / `.css` | CSS del framework | `valk/styles.php` |
| `cache/sx1.0.css` | todos los `gate/css/sx*.css` | `valk/styles.php:47-70` |

**Invalidación**: solo por borrado manual del directorio, vía la función `CleanCache`
(`valk/ro/rDebug.php:13-22` y `valk/mu/v1.0/Kirito.php:19-27`), que es invocable
**por cualquiera** desde `/g` (ver [SEC-01](08-seguridad.md)).

⚠️ Consecuencia operativa: **cualquier cambio en JS o CSS requiere borrar `cache/` a mano**,
o subir la versión en `manifest.php` (`$Manifest['Version']['Js']`).
