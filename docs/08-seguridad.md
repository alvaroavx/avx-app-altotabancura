---
doc: revision-de-seguridad
lectura_previa: 04-roles-y-permisos.md
naturaleza: revisión defensiva por análisis estático de código (SAST manual)
alcance: código del repositorio en el commit 37614c4
fuera_de_alcance: base de datos, configuración del servidor, red, pruebas dinámicas
---

# 08 · Revisión de seguridad

> ## 🚨 Conclusión ejecutiva
>
> **Esta aplicación no debe estar expuesta a Internet en su estado actual.**
>
> Maneja **datos de salud identificables** (nombre, RUT, fecha de nacimiento, vacunas
> recibidas, lote y fecha de aplicación) y presenta **fallas de control de acceso que
> permiten a cualquier persona sin autenticarse leer, modificar y borrar esos datos**,
> además de al menos una vía plausible de **ejecución de código en el servidor**.
>
> Las mitigaciones inmediatas están en [§5](#5-plan-de-remediación).
>
> Este documento describe **qué está mal y cómo corregirlo**. No incluye exploits
> funcionales; su propósito es la remediación por parte de quien opera el sistema.

## 1. Resumen de hallazgos

| ID | Hallazgo | Severidad | CWE | Explotable sin sesión |
|---|---|:---:|---|:---:|
| [SEC-01](#sec-01--control-de-acceso-inexistente-en-el-dispatcher) | Control de acceso inexistente en `/g` | 🔴 **Crítica** | CWE-306, CWE-862 | ✅ Sí |
| [SEC-02](#sec-02--subida-de-archivos-sin-autenticar-con-destino-y-tipo-controlados-por-el-cliente) | Subida de archivos sin autenticar, con tipo y destino elegidos por el cliente | 🔴 **Crítica** | CWE-434, CWE-22 | ✅ Sí |
| [SEC-03](#sec-03--acceso-directo-a-certificados-de-terceros-idor) | Acceso a datos y certificados de terceros (IDOR) | 🔴 **Crítica** | CWE-639, CWE-200 | ✅ Sí |
| [SEC-04](#sec-04--credenciales-y-claves-criptográficas-en-el-repositorio) | Credenciales de producción y claves criptográficas en el repositorio | 🔴 **Crítica** | CWE-798 | n/a |
| [SEC-05](#sec-05--inclusión-de-archivos-por-ruta-controlada-o) | Inclusión de archivos por ruta controlada (`/o/`) | 🔴 **Crítica** | CWE-98, CWE-22 | ✅ Sí |
| [SEC-06](#sec-06--credenciales-por-defecto-derivadas-del-rut) | Credenciales por defecto derivadas del RUT | 🟠 Alta | CWE-1392, CWE-521 | ✅ Sí |
| [SEC-07](#sec-07--todo-el-tráfico-viaja-sin-cifrar-http) | Todo el tráfico viaja sin cifrar (HTTP) | 🟠 Alta | CWE-319 | n/a |
| [SEC-08](#sec-08--cross-site-scripting-xss-almacenado-y-reflejado) | XSS almacenado y reflejado | 🟠 Alta | CWE-79 | ✅ Sí |
| [SEC-09](#sec-09--ausencia-total-de-protección-csrf) | Ausencia total de protección CSRF | 🟠 Alta | CWE-352 | ✅ Sí |
| [SEC-10](#sec-10--gestión-de-sesión-insegura) | Gestión de sesión insegura (fijación, cookies sin flags) | 🟠 Alta | CWE-384, CWE-1004 | ✅ Sí |
| [SEC-11](#sec-11--código-de-validación-del-certificado-predecible) | Código de validación del certificado predecible (`md5(RUT)`) | 🟠 Alta | CWE-330, CWE-916 | ✅ Sí |
| [SEC-12](#sec-12--divulgación-de-información-sensible) | Divulgación de información sensible | 🟠 Alta | CWE-200, CWE-209 | ✅ Sí |
| [SEC-13](#sec-13--manejo-inseguro-de-contraseñas) | Manejo inseguro de contraseñas | 🟠 Alta | CWE-916, CWE-257 | — |
| [SEC-14](#sec-14--componentes-sin-soporte-y-sin-parchar) | Componentes sin soporte y sin parchar (PHP 5.6, vendor/) | 🟠 Alta | CWE-1104, CWE-1035 | ❓ |
| [SEC-15](#sec-15--sin-límite-de-tasa-y-enumeración-de-usuarios) | Sin límite de tasa; enumeración de usuarios | 🟡 Media | CWE-307, CWE-204 | ✅ Sí |
| [SEC-16](#sec-16--retención-indefinida-de-datos-de-salud-en-disco) | Retención indefinida de datos de salud en disco | 🟡 Media | CWE-212, CWE-552 | n/a |
| [SEC-17](#sec-17--despacho-dinámico-y-concatenación-en-la-capa-de-datos) | Despacho dinámico y concatenación en la capa de datos (SQLi latente) | 🟡 Media | CWE-89, CWE-470 | ❌ hoy no |
| [SEC-18](#sec-18--directorios-web-con-escritura-y-generación-de-php) | Directorios web con permiso de escritura y generación de `.php` | 🟡 Media | CWE-434, CWE-732 | ❌ hoy no |
| [SEC-19](#sec-19--cabeceras-de-seguridad-ausentes) | Cabeceras de seguridad ausentes | 🟡 Media | CWE-693 | n/a |
| [SEC-20](#sec-20--correo-desviado-a-un-buzón-de-terceros) | Correo desviado a un buzón de terceros | 🟡 Media | CWE-201 | n/a |
| [SEC-21](#sec-21--sin-auditoría-de-accesos-a-datos-clínicos) | Sin auditoría de accesos a datos clínicos | 🟡 Media | CWE-778 | n/a |

---

## 2. Hallazgos críticos

### SEC-01 · Control de acceso inexistente en el dispatcher

**Severidad: 🔴 Crítica — este es el hallazgo raíz del que dependen varios otros.**

`valk/gatekeeper.php` es el único punto de entrada de la aplicación y su lógica es,
en esencia:

```php
// valk/gatekeeper.php — flujo simplificado
$raw_data = DecodePost($_POST['rawdata']);   // sin validar
// … incluye TODOS los r*.php y x*.php …
call_user_func($raw_data['valk'], $raw_data); // ⚠️ sin comprobar sesión ni rol
```

**No existe ninguna comprobación de autenticación ni de autorización antes del
`call_user_func`.** Cualquier función global definida en los archivos incluidos
—unas 85, listadas en [`07-catalogo-endpoints.md`](07-catalogo-endpoints.md)— es
invocable por cualquier persona que pueda hacer un `POST` al sitio.

El único control que existe, `FiltrarSesion()`, **no interrumpe la ejecución**:

```php
// valk/tools/tSesion.php:7-11
function FiltrarSesion(){
    if (! ValidaSesion()) {
        echo '<script>$.LoadMidBlock(1);</script>';   // ⚠️ falta return/exit/die
    }
}
```

Emite un `<script>` que pide al **navegador** volver al login, pero la función que la
invocó sigue ejecutándose, consulta la base de datos y devuelve el HTML con los datos.
Un cliente que no ejecute JavaScript (cualquier herramienta de línea de comandos)
recibe la información completa.

**Impacto concreto, sin ninguna credencial:**

| Acción | Función | Consecuencia |
|---|---|---|
| Listar todos los alumnos con RUT y fecha de nacimiento | `LoadResultadosPaginado` con `tamanopagina=500` | Exfiltración masiva de datos personales |
| Ver las vacunas de cualquier persona | `VacunasFormularioUsuario` | Exfiltración de **datos de salud** |
| Crear un usuario administrador | `CreateModifyUsuario` con `IdTipoUsuario=3` | **Toma de control de la aplicación** |
| Borrar cualquier usuario o catálogo | `RemoveUsuario`, `Remove*` | Destrucción de datos |
| Alterar el registro de vacunación | `UserVaccine`, `DetachVaccine` | **Falsificación de registro clínico** |
| Fijar la sesión con cualquier identidad | `IniciarSesion` | Suplantación |
| Volcar `$_SESSION`, `$_COOKIE` y configuración | `DatosSesion` | Reconocimiento del sistema |

**Corrección:** ver [§5, paso 2](#5-plan-de-remediación) — lista blanca explícita
`función → roles`, denegación por defecto, evaluada **antes** del `call_user_func`.

---

### SEC-02 · Subida de archivos sin autenticar, con destino y tipo controlados por el cliente

**Severidad: 🔴 Crítica**

`valk/ro/rUploader.php` es un **archivo físico accesible directamente por HTTP**
(la reescritura de `.htaccess` solo redirige lo que *no* existe en disco). No incluye
`loader.php`, no comprueba sesión, y construye sus opciones así:

```php
// valk/ro/rUploader.php:3-10
$options = array(
    'accept_file_types' => (($_REQUEST['file_type']) ? '/\.('.$_REQUEST['file_type'].')/' : '…'),
    'max_file_size'     => 50 * 1000000,
    'upload_dir'        => '../../'.(($_REQUEST['dir_url']) ? $_REQUEST['dir_url'] : constant('Root_Static')),
    'upload_url'        => '../../'.(($_REQUEST['dir_url']) ? $_REQUEST['dir_url'] : constant('Root_Static')),
    'file_name'         => $_REQUEST['file_name']
);
$upload_handler = new UploadHandler($options);
```

Tres decisiones de seguridad quedan **en manos de quien envía la petición**:

1. **`accept_file_types`** — la lista de extensiones permitidas se interpola desde
   `$_REQUEST['file_type']`. El atacante define qué extensiones son válidas, incluidas
   las ejecutables por el servidor. La expresión ni siquiera está anclada al final
   (`/\.(…)/` sin `$`), por lo que también acepta dobles extensiones.
2. **`upload_dir`** — el directorio destino se concatena desde `$_REQUEST['dir_url']`
   sin normalizar ni validar, con un `../../` de base. Permite escribir **fuera** del
   directorio previsto, en cualquier ruta accesible al usuario del servidor web.
3. **`file_name`** — nombre del archivo destino tomado del cliente.

Combinado con [SEC-05](#sec-05--inclusión-de-archivos-por-ruta-controlada-o), un
archivo así colocado puede llegar a ser **ejecutado como código PHP** en el servidor,
lo que equivale a compromiso total del sitio y acceso a las credenciales de la base de datos.

`UploadHandler` (blueimp) también expone, por diseño, operaciones `GET` (listar
archivos) y `DELETE` (borrar archivos) en el mismo endpoint, igualmente sin autenticación.

> 📌 **Este endpoint no lo usa la aplicación.** `$.Uploader` (`valk.core.js:180`) es la
> única referencia y **ningún elemento de la interfaz la invoca**. Es funcionalidad
> heredada del framework. **La corrección correcta es eliminar el archivo.**

**Corrección inmediata:** borrar `valk/ro/rUploader.php`, `valk/mu/cUploadHandler.php`,
`img/user/UploadHandler.php` e `img/user/index.php`. Si en el futuro se necesita subida
de archivos: exigir sesión, lista blanca de extensiones **fija en el código**, directorio
destino **fijo y fuera del document root**, nombre generado por el servidor, y validación
del contenido real del archivo.

---

### SEC-03 · Acceso directo a certificados de terceros (IDOR)

**Severidad: 🔴 Crítica**

Dos problemas encadenados:

**(a) La generación usa el identificador que envía el cliente, no el de la sesión:**

```php
// gate/omega/xCertificado.php:70-73
$NombreArchivo = str_replace(' ', '.', 'certificado'.$data['idusuario']);
$URL = constant('Root_Base').constant('Prefix_Out').'/'.$NombreArchivo.'.pdf';
CrearCertificado($data['idusuario'], $Certificado, $NombreArchivo);
```

Un alumno autenticado (o alguien sin sesión, por [SEC-01](#sec-01--control-de-acceso-inexistente-en-el-dispatcher))
puede pedir el certificado de **cualquier** `IdUsuario`. Con `multiple=1` y una lista
de identificadores, obtiene un único PDF con los datos de **muchas personas a la vez**.
El identificador es un entero secuencial, por lo que recorrer todo el padrón es trivial.

**(b) El PDF resultante queda accesible públicamente con nombre predecible:**

El archivo se escribe en `out/certificado<IdUsuario>.pdf` y se sirve por
`GET /o/certificado<IdUsuario>.pdf` **sin ninguna comprobación** (`valk/alphonse.php:67-70`).
Los PDFs consolidados usan `consolidado<timestamp>.pdf`, también adivinable.

Cada PDF contiene: nombre completo, RUT, y el detalle de vacunas con fechas y lotes —
**datos sensibles de salud** bajo la legislación chilena (ver [§4](#4-contexto-regulatorio-chileno)).

**Corrección:**
1. En `GenerarCertificado`, **ignorar** `$data['idusuario']` cuando el perfil es `alumno`
   y usar siempre `Sesion('idusuario')`.
2. Para docentes: verificar en el servidor que el alumno solicitado pertenece a la
   universidad de la sesión (`Sesion('universidad')`).
3. Servir los PDF a través de una función PHP que valide la sesión y la pertenencia,
   **no** por URL estática; o mover `out/` fuera del *document root*.
4. Usar nombres de archivo aleatorios y de un solo uso (p. ej. token de 128 bits).

---

### SEC-04 · Credenciales y claves criptográficas en el repositorio

**Severidad: 🔴 Crítica**

| Secreto | Ubicación | Estado |
|---|---|---|
| Host, base, usuario y **contraseña de SQL Server de producción** | `valk/mu/v1.0/Steins.php:8-14` | 🔴 en texto plano, versionado en Git |
| Credenciales de la base local | `valk/mu/v1.0/Steins.php:16-22` | 🔴 idem |
| Clave e IV de AES-256-CBC | `valk/default/dKeys.php:3-4` y `valk/keys.php:9-10` (duplicada) | 🔴 idem |
| ID de Google Analytics y de Facebook Pixel | `gate/keys.php:10-11` | 🟡 menos sensibles |
| Correo de soporte usado como buzón de desvío | `valk/mu/v1.0/Mail.php:88` | 🟡 |
| Correo personal en copia oculta fija | `valk/mu/v1.0/Mail.php:82` | 🟡 |

Consideraciones adicionales:

- Las credenciales de BD están en un archivo **dentro del *document root***. Ante cualquier
  fallo de configuración que sirva `.php` como texto plano, quedan expuestas por HTTP.
- La clave AES **es la misma para todos los proyectos** que usen este framework (viene
  del archivo de valores por defecto). Cualquiera con acceso al repositorio puede
  descifrar y **forjar** los payloads de `/e/…` y de `valk/gate.php`.
- Por la mecánica de `define()` (primera definición gana), el archivo `valk/keys.php`
  —pensado como el lugar "propio" de las claves— **nunca se aplica**; siempre rigen las
  del archivo de defaults. Ver [`01-arquitectura.md`](01-arquitectura.md) §6.

**Corrección:**
1. **Asumir todos estos secretos como comprometidos y rotarlos** (contraseña de BD, clave/IV de AES).
2. Mover la configuración sensible a variables de entorno o a un archivo **fuera del
   document root**, no versionado, y añadirlo a `.gitignore`.
3. Crear un usuario de base de datos con permisos mínimos: solo `EXECUTE` sobre los
   procedimientos necesarios, sin `db_owner` ni acceso a tablas.
4. Purgar los secretos del historial de Git (`git filter-repo`) o, si el repositorio ya
   fue compartido, considerarlos permanentemente quemados.

---

### SEC-05 · Inclusión de archivos por ruta controlada (`/o/`)

**Severidad: 🔴 Crítica**

El router construye una ruta de archivo concatenando directamente un segmento de la URL
y termina haciendo `include()` sobre ella:

```php
// valk/alphonse.php:67-71
else if (preg_match('/'.'\/'.constant('Prefix_Out').'\/.+(?!\..+)'.'/', $Url)) {
    $Url  = str_replace('/'.constant('Prefix_Out').'/', '', $Url);
    $Ruta = constant('Root_Fisica').constant('Root_Out').$Url;   // ⚠️ sin normalizar
}
…
// valk/alphonse.php:161-166
if($Ruta != '' && file_exists($Ruta)){
    switch($Extension){ … }   // solo fija cabeceras para extensiones conocidas
    include($Ruta);           // ⚠️ INCLUDE, no readfile()
    exit();
}
```

Dos problemas:

1. **No hay normalización de la ruta.** El segmento posterior a `/o/` se concatena tal
   cual, de modo que secuencias de recorrido de directorios permiten salir de `out/` y
   alcanzar otros archivos del servidor.
2. **Se usa `include()` en lugar de `readfile()`.** `include()` **ejecuta** el archivo
   como PHP, sin importar su extensión. Un archivo `.pdf` o `.txt` que contenga código
   PHP se ejecutaría al ser solicitado por esta ruta.

Encadenado con [SEC-02](#sec-02--subida-de-archivos-sin-autenticar-con-destino-y-tipo-controlados-por-el-cliente),
esto constituye una vía plausible de **ejecución remota de código**.

El mismo patrón `include($Ruta)` afecta a las demás ramas del router (`/res/`, `/static/`,
`/k/`), aunque allí las expresiones regulares exigen extensiones de imagen o de asset.

**Corrección:**
1. Sustituir `include($Ruta)` por `readfile($Ruta)` para todo contenido no ejecutable.
2. Normalizar y confinar: `realpath($Ruta)` y verificar que el resultado empiece por
   `realpath(Root_Fisica . Root_Out)`; rechazar si no.
3. Validar el nombre solicitado contra una lista blanca de caracteres (`[A-Za-z0-9._-]+`)
   y rechazar cualquier `.` `.` consecutivo o barra.
4. Servir `out/` con autenticación (ver SEC-03) o desde fuera del document root.

---

## 3. Hallazgos altos y medios

### SEC-06 · Credenciales por defecto derivadas del RUT

🟠 **Alta.** La portada pública explica el esquema de credenciales por defecto
(`gate/omega/xDashboard.php:229-231`):

> *"En el campo usuario ingresa tu Rut sin puntos ni dígito verificador… En el campo
> contraseña, ingresa los primeros 4 dígitos de tu Rut"*

Es decir: **el usuario y la contraseña se derivan ambos del mismo dato**, un identificador
nacional que no es secreto y que aparece en documentos, listados y bases filtradas.
Conocer el RUT de una persona equivale a conocer sus credenciales, salvo que la haya cambiado
—y **el flujo de cambio de contraseña está roto** (ver [F-07](05-flujos-funcionales.md#f-07--recuperación-de-contraseña-inoperante)),
por lo que en la práctica **nadie puede haberla cambiado por sí mismo**.

Además, la contraseña efectiva son 4 dígitos ⇒ 10 000 combinaciones como máximo, y en
realidad menos: son los 4 primeros dígitos del RUT, fuertemente correlacionados con la edad.

**Corrección:** forzar cambio de contraseña en el primer ingreso; exigir un segundo
factor de verificación de identidad (p. ej. fecha de nacimiento + correo institucional);
reparar el flujo de recuperación; política mínima de contraseñas.

### SEC-07 · Todo el tráfico viaja sin cifrar (HTTP)

🟠 **Alta.** `$Manifest['Root'] = 'http://vacunatorioaltotabancura.cl/'` (`manifest.php:14`).
Sin TLS, viajan en claro por la red: credenciales de login, cookie de sesión, RUT,
nombres, fechas de nacimiento y el detalle de vacunación. El "cifrado" del payload
`rawdata` es **solo Base64** — no aporta confidencialidad alguna.

**Corrección:** certificado TLS (Let's Encrypt es gratuito), redirección 301 de HTTP a
HTTPS, cabecera `Strict-Transport-Security`, y cookie de sesión con el atributo `Secure`.

### SEC-08 · Cross-Site Scripting (XSS) almacenado y reflejado

🟠 **Alta.** **Toda** la capa de presentación construye HTML por concatenación de
cadenas, **sin una sola llamada a `htmlspecialchars()` sobre datos de la base**:

```php
// gate/omega/xUsuario.php:189 — patrón repetido en los 9 controladores
echo '<td>' . $usuario['Nombres'] . ' ' . $usuario['Apellidos'] . '</td>';
```

Peor aún, varios valores se inyectan **dentro de atributos de evento JavaScript**:

```php
// gate/omega/xUsuario.php:196
'<div class="icon small" onclick="$.LoadUsuario('.$usuario['IdUsuario'].')">'
// gate/omega/xSede.php:9
'<div onclick="$.ResultadosPorSede(this, '.$S['IdSede'].')" …>'
```

Vectores identificados:
- **Almacenado**: el nombre de un alumno, de una universidad, sede, carrera o vacuna se
  guarda vía `CreateModify*` (accesible sin autenticar, SEC-01) y se ejecuta en el
  navegador de **cualquier administrador** que abra la grilla ⇒ escalada a cuenta admin.
- **Reflejado**: `LoadPaginacion` interpola `$data['tamanopagina']` y `$data['numeropagina']`
  —valores del cliente— directamente en HTML y en atributos `onclick` (`xUsuario.php:405-425`).

Las únicas dos llamadas a `htmlspecialchars()` del proyecto están en el login social
desactivado (`valk/ro/rLogin.php:20,23`).

⚠️ Nota: `LimpiaHtml()` (aplicada a toda entrada) **no es una defensa**: convierte
entidades HTML **a** caracteres literales — es decir, va en la dirección contraria a
un escapado.

**Corrección:** función de escapado obligatoria (`htmlspecialchars($v, ENT_QUOTES, 'UTF-8')`)
aplicada a **todo** valor interpolado; para atributos numéricos, forzar `(int)`; a medio
plazo, sustituir la concatenación por un motor de plantillas con auto-escapado.

### SEC-09 · Ausencia total de protección CSRF

🟠 **Alta.** No existe ningún token anti-CSRF en el proyecto (búsqueda de `token`, `nonce`,
`csrf`: sin resultados en el flujo de peticiones). Todas las operaciones de escritura son
`POST` a `/g` con un cuerpo `application/x-www-form-urlencoded` — un tipo de contenido que
**puede enviarse desde un sitio de terceros sin *preflight* CORS**.

Un administrador autenticado que visite una página maliciosa puede, sin saberlo, ejecutar
`RemoveUsuario`, `CreateModifyUsuario` o `UserVaccine` con su propia sesión.

**Corrección:** token CSRF por sesión, incluido en cada payload de `$.RawData` y validado
en `gatekeeper.php` para toda función que modifique estado; y `SameSite=Strict` en la cookie.

### SEC-10 · Gestión de sesión insegura

🟠 **Alta.**

| Problema | Detalle |
|---|---|
| **Fijación de sesión** | `IniciarSesion()` (`rLogin.php:74`) no llama a `session_regenerate_id(true)`: el identificador previo al login sigue siendo válido después. |
| **Cookie sin `HttpOnly`** | No se configura `session.cookie_httponly` ⇒ la cookie es legible por JavaScript ⇒ el XSS de SEC-08 permite robarla. |
| **Cookie sin `Secure` ni `SameSite`** | Viaja por HTTP y se envía en peticiones de terceros. |
| **Sin caducidad propia** | Depende únicamente del `session.gc_maxlifetime` del servidor; el heartbeat que debía vigilarla **se ejecuta una sola vez** (su bucle está comentado, `valk.watchdog.js:15-30`). |
| **`IniciarSesion` es invocable externamente** | Está definida como función global en un archivo `r*.php` ⇒ accesible desde `/g` (SEC-01). |

**Corrección:** `session_regenerate_id(true)` tras autenticar; `session_set_cookie_params`
con `httponly=true, secure=true, samesite='Strict'`; caducidad por inactividad;
sacar `IniciarSesion` del conjunto de funciones invocables.

### SEC-11 · Código de validación del certificado predecible

🟠 **Alta.** El "código de validación" impreso en cada certificado es:

```php
$Codigo = hash('md5', $Usuario['Rut']);   // gate/omega/xCertificado.php:20
```

Consecuencias:
- Es **determinístico**: el mismo RUT produce siempre el mismo código, en todas las
  emisiones y para siempre. No identifica una emisión concreta.
- Es **invertible en la práctica**: el espacio de RUTs chilenos válidos es pequeño
  (del orden de decenas de millones); una tabla precalculada de MD5 se construye en minutos.
  Es decir, **el código publicado en el certificado revela el RUT del titular**.
- MD5 no es apto para este uso (rápido, sin sal, con colisiones conocidas).
- No hay ningún mecanismo de verificación: no existe pantalla ni endpoint donde un
  tercero pueda validar un código, por lo que el campo **no cumple su función declarada**.

**Corrección:** generar un token aleatorio por emisión (`random_bytes(16)` en hexadecimal),
almacenarlo asociado a la emisión, y publicar una página de verificación que reciba el
token y confirme validez y fecha **sin revelar datos personales**.

### SEC-12 · Divulgación de información sensible

🟠 **Alta.** Múltiples vías:

| Vía | Detalle | Ubicación |
|---|---|---|
| Función `DatosSesion` | Imprime `$_SESSION`, `$_COOKIE`, `$Manifest` y `$Var` completos. Invocable sin sesión. | `valk/ro/rDebug.php:2-12` |
| `log.txt` en la raíz web | Log de aplicación descargable por HTTP | `valk/tools/tCore.php:23-45` |
| `test.php` y `valk/test.php` | Scripts de depuración accesibles; `valk/test.php` **imprime el manifiesto y ejecuta consultas** | raíz y `valk/` |
| Errores de SQL Server renderizados | `SQLSTATE`, código y mensaje del motor se devuelven como HTML al cliente | `valk/mu/v1.0/SqlServer.php:44-52` |
| `web.config` con errores detallados | `<httpErrors errorMode="Detailed"/>` y `<customErrors mode="Off"/>` ⇒ trazas de .NET/IIS al usuario | `web.config:16-19` |
| Función `Edward` | Con `Entorno_Developer=1`, imprime **todos los parámetros de cada petición** en la consola del navegador | `valk/tools/tDebug.php` |
| Directorios sin `index` | `cache/`, `res/`, `img/`, `vendor/` pueden ser listables según configuración del servidor | ❓ |

**Corrección:** eliminar `rDebug.php`, `test.php`, `valk/test.php`; mover `log.txt` fuera
del document root; capturar los errores de BD y devolver un mensaje genérico registrando
el detalle en el log; `customErrors mode="On"` y `errorMode="Custom"`; denegar listado de directorios.

### SEC-13 · Manejo inseguro de contraseñas

🟠 **Alta.**

- El **algoritmo de hash es desconocido** (vive en el procedimiento almacenado). El único
  indicio en el código es `md5($data['pass'])` en `RecoverPassPush` (`rLogin.php:307`),
  lo que sugiere **MD5 sin sal** — inadecuado desde hace más de una década.
- **Inconsistencia grave**: `Autentificar` envía la contraseña **sin transformar**
  (`rLogin.php:50`) mientras `RecoverPassPush` envía un MD5. Uno de los dos caminos
  produce contraseñas que no validan.
- Al crear o editar un docente, la contraseña viaja **en texto plano** dentro del payload
  y se **rellena en el formulario** como `value="******"` (`xUsuario.php:599`), con la
  convención de que `'******'` significa "no cambiar" (`jxUsuario.js`, `$.SaveDocente`)
  — una contraseña real igual a `******` sería ignorada silenciosamente.
- El campo de contraseña del formulario de docente es `type="text"` (visible en pantalla).

**Corrección:** migrar a `password_hash()` / `password_verify()` (bcrypt o Argon2) con
re-hash progresivo en el login; unificar el punto donde se hashea (siempre en el servidor,
nunca en el cliente); usar `type="password"`; no devolver nunca el valor del campo al cliente.

### SEC-14 · Componentes sin soporte y sin parchar

🟠 **Alta.**

| Componente | Situación |
|---|---|
| **PHP 5.6** | Sin soporte de seguridad desde el **31-12-2018**. Todas las vulnerabilidades posteriores del intérprete están sin parchar. |
| **PHPMailer** (`vendor/PHPMailer/`) | Versión ❓ no declarada. Las versiones < 5.2.18 tienen vulnerabilidades de ejecución remota ampliamente explotadas (CVE-2016-10033 y familia). **Verificar la versión con prioridad.** |
| **jQuery 1.x** | Versión ❓ no declarada. Las versiones < 3.5.0 tienen XSS conocidos (CVE-2020-11022/11023). |
| **Facebook SDK v5 / Google API Client** | Sin mantenimiento; el `doorlock.php` de Facebook está **corrupto** (bytes binarios). Código muerto. |
| **blueimp UploadHandler** | Versión ❓; histórico de vulnerabilidades de subida arbitraria. |
| **Sin gestor de dependencias** | No hay forma sistemática de saber qué versión hay ni de actualizar. |

**Corrección:** inventariar versiones reales; eliminar todo lo que sea código muerto
(Facebook, Google, Jodit, UploadHandler); adoptar Composer para lo que se conserve;
planificar la migración de intérprete (ver [`09-mejoras-deuda-tecnica.md`](09-mejoras-deuda-tecnica.md) MEJ-01).

### SEC-15 · Sin límite de tasa y enumeración de usuarios

🟡 **Media.** La aplicación no implementa ningún control de frecuencia: ni en el login,
ni en la generación de PDFs (que además **escribe en disco**, lo que la convierte en un
vector de agotamiento de espacio), ni en `WDTest` (que **añade líneas a `log.txt`** en cada llamada).

El bloqueo por 4 intentos vive en el procedimiento almacenado y es **por cuenta**, no por
origen: no impide probar una contraseña contra miles de cuentas distintas (*password spraying*),
que es precisamente el escenario relevante dado SEC-06.

Además, los mensajes diferenciados ("Quedan N intentos" vs. "Usuario o contraseña incorrecta")
**revelan si una cuenta existe** (`rLogin.php:57-70`).

**Corrección:** límite de tasa por IP y por cuenta; retardo progresivo; mensajes de error
uniformes; CAPTCHA tras N fallos; cuota de generación de PDFs por sesión.

### SEC-16 · Retención indefinida de datos de salud en disco

🟡 **Media.** Cada certificado emitido deja un PDF permanente en `out/`, con datos de
salud identificables, **sin política de expiración ni proceso de limpieza**. Los PDFs
consolidados pueden contener cientos de personas en un solo archivo. Todo ello es
accesible por URL sin autenticación (SEC-03).

**Corrección:** generar el PDF en memoria y entregarlo en la respuesta (`FPDF::Output('S')`)
en lugar de escribirlo; si debe persistirse, hacerlo fuera del document root con expiración
automática y una tarea de purga.

### SEC-17 · Despacho dinámico y concatenación en la capa de datos

🟡 **Media (riesgo latente, no explotable hoy).**

- `SqlServer::Ejecutar()` liga los **valores** como parámetros (✅ correcto), pero
  **concatena el nombre del procedimiento y los nombres de los parámetros** en la
  cadena SQL (`SqlServer.php:26-41`). Hoy ambos son literales del código fuente, por lo
  que **no hay inyección SQL explotable**. Pero cualquier cambio futuro que permita
  elegir el procedimiento desde la petición abriría una inyección inmediata.
- Todos los módulos de `valk/mu/v1.0/` ejecutan `$this->$Metodo()` con el nombre recibido
  del llamador (`Login.php:16`, `Zeus.php:17`, etc.). Está acotado por `method_exists`
  a métodos de la propia clase, pero es un patrón frágil.
- `Valk::LoadModo()` hace `new $this->Modo(...)` (`cValk.php:44`) previo `LoadModulo()`,
  que hace `require_once` de una ruta construida con el nombre del módulo. La cadena
  `Modo → nombre de archivo` está bajo control del código, pero es el mismo patrón de riesgo.

**Corrección:** mantener la regla invariable de que **nombres de procedimiento y de módulo
nunca provienen de la petición**; documentarla; añadir una lista blanca explícita de
procedimientos permitidos en `Query`.

### SEC-18 · Directorios web con escritura y generación de `.php`

🟡 **Media.** La aplicación **escribe archivos PHP dentro del document root en tiempo de
ejecución**: `cache/jv1.0.php` y `cache/sv1.0.php` se generan concatenando cadenas y luego
se ejecutan con `include()` (`valk/scripts.php:2-20`, `valk/styles.php:3-22`).

Hoy el contenido proviene de literales del propio código (`Scripts.php`, `Styles.php`),
por lo que no es explotable directamente. Pero la combinación "directorio web con permiso
de escritura + archivos `.php` generados + `include()`" es exactamente el patrón que
convierte cualquier escritura arbitraria de archivos (SEC-02) en ejecución de código.

**Corrección:** mover `cache/` fuera del document root; almacenar la lista de assets en
un formato de datos (JSON) leído con `json_decode`, no en PHP ejecutable; denegar la
ejecución de PHP en `cache/`, `out/` y `static/` mediante configuración del servidor.

### SEC-19 · Cabeceras de seguridad ausentes

🟡 **Media.** El proyecto no emite ninguna cabecera de seguridad:
`Content-Security-Policy`, `X-Content-Type-Options`, `X-Frame-Options`,
`Referrer-Policy`, `Strict-Transport-Security` y `Permissions-Policy` están todas ausentes.

La única protección contra *clickjacking* es un *frame-buster* en JavaScript
(`valk.core.js:1-3`), evadible con el atributo `sandbox` de `<iframe>`.

Además, `alphonse.php` fija `Cache-Control: public, max-age=31536000` para assets
(`:141`), pero las respuestas de `/g` —que contienen datos personales— **no llevan
`Cache-Control: no-store`**, por lo que pueden quedar en cachés intermedias y en el disco del navegador.

**Corrección:** añadir las cabeceras en `index.php`/`gatekeeper.php` o en la configuración
del servidor; `no-store` para toda respuesta con datos personales.

### SEC-20 · Correo desviado a un buzón de terceros

🟡 **Media.** El módulo de correo tiene un modo *bypass* **forzado con una condición
imposible** (`if (1 == 2)`, `Mail.php:44`): **todo** correo que el sistema intente enviar
—incluidos los de recuperación de contraseña— se redirige a `soporte@steins.cl`, con los
destinatarios reales listados en el cuerpo del mensaje. La rama desactivada añade además
una copia oculta fija a una dirección personal (`njong1@gmail.com`, `Mail.php:75`).

En la práctica el envío no funciona (faltan las credenciales SMTP, ver
[F-07](05-flujos-funcionales.md#f-07--recuperación-de-contraseña-inoperante)), pero si
alguien reparara la configuración **sin quitar el bypass**, los datos de los usuarios se
enviarían a un buzón externo al responsable del tratamiento.

**Corrección:** eliminar el bloque de bypass y la copia oculta fija; controlar el modo de
prueba mediante la constante `Entorno_BypassCorreo` (que existe y **nunca se lee**);
configurar las credenciales SMTP en `Steins::Gate()`, que hoy no las devuelve.

### SEC-21 · Sin auditoría de accesos a datos clínicos

🟡 **Media.** El framework incluye un subsistema de auditoría (`Morty.php`, procedimientos
`spIns_Log_Log` / `spRec_Log_Log`) que **ninguna parte de la aplicación invoca**. No hay
registro de quién consultó la ficha de quién, quién emitió un certificado, ni quién
modificó un registro de vacunación.

Para un sistema que trata datos de salud, la trazabilidad no es opcional: sin ella es
imposible detectar un acceso indebido, dimensionar una brecha o cumplir con una solicitud
de información del titular.

**Corrección:** registrar, como mínimo: autenticaciones (éxito y fallo), consultas de
ficha, emisiones de certificado y toda operación de escritura, con usuario, IP, marca de
tiempo y recurso afectado. El subsistema ya existe: basta conectarlo.

---

## 4. Contexto regulatorio chileno

> ⚠️ Esto es **contexto técnico, no asesoría legal.** Corresponde validar con un
> especialista, pero el equipo debe conocer el marco.

Este sistema trata **datos personales sensibles** (datos de salud) de personas
identificadas por su RUT. En Chile son relevantes al menos:

| Norma | Relevancia para este sistema |
|---|---|
| **Ley 19.628** (protección de la vida privada) | Clasifica los datos relativos a la salud como **sensibles**, con exigencias reforzadas de tratamiento y seguridad. |
| **Ley 20.584** (derechos y deberes del paciente) | Regula la **ficha clínica** y su reserva; el acceso indebido a información de salud tiene consecuencias específicas. |
| **Ley 21.719** (nueva ley de protección de datos, publicada en 2024, con entrada en vigencia diferida) | Introduce la **Agencia de Protección de Datos Personales**, obligaciones de **medidas de seguridad**, **notificación de brechas** y un régimen de **multas significativas**. |

Las brechas descritas en [SEC-01](#sec-01--control-de-acceso-inexistente-en-el-dispatcher),
[SEC-03](#sec-03--acceso-directo-a-certificados-de-terceros-idor) y
[SEC-16](#sec-16--retención-indefinida-de-datos-de-salud-en-disco) implican que datos de
salud identificables son accesibles **sin autenticación** desde Internet. Si el sitio está
efectivamente publicado, esto configura una exposición que muy probablemente deba tratarse
como **incidente de seguridad notificable** bajo el marco vigente.

**Recomendación operativa inmediata:** si el sitio está en línea, considerar **restringir
el acceso público** (mantenimiento, filtro por IP o autenticación en el servidor web)
mientras se aplican las correcciones críticas.

---

## 5. Plan de remediación

### Fase 0 — Contención (horas)

| # | Acción | Corrige |
|---|---|---|
| 0.1 | Restringir el acceso público al sitio mientras se corrige | todos |
| 0.2 | **Eliminar** `valk/ro/rUploader.php`, `valk/mu/cUploadHandler.php`, `img/user/UploadHandler.php`, `img/user/index.php` | SEC-02 |
| 0.3 | **Eliminar** `test.php`, `valk/test.php`, `valk/ro/rDebug.php` | SEC-12 |
| 0.4 | Mover `log.txt` fuera del document root | SEC-12 |
| 0.5 | Denegar por configuración del servidor el acceso HTTP a `valk/`, `gate/class/`, `gate/omega/`, `vendor/` (salvo `.js`/`.css`) | SEC-04, SEC-12 |
| 0.6 | **Rotar** la contraseña de SQL Server y la clave/IV de AES | SEC-04 |
| 0.7 | Vaciar `out/` y denegar su acceso directo | SEC-03, SEC-16 |

### Fase 1 — Correcciones críticas (días)

| # | Acción | Corrige |
|---|---|---|
| 1.1 | **Autorización en `gatekeeper.php`**: lista blanca `función → roles`, denegación por defecto (esquema abajo) | SEC-01 |
| 1.2 | `FiltrarSesion()` debe **detener la ejecución** (`exit`) tras emitir el script | SEC-01 |
| 1.3 | `GenerarCertificado`: usar `Sesion('idusuario')` para alumnos; validar pertenencia para docentes | SEC-03 |
| 1.4 | `alphonse.php`: `readfile()` en vez de `include()`; `realpath()` + confinamiento de la ruta | SEC-05 |
| 1.5 | Sacar credenciales de `Steins.php` a configuración fuera del repositorio; usuario de BD con permisos mínimos | SEC-04 |
| 1.6 | Instalar TLS + redirección + HSTS + cookie `Secure` | SEC-07 |
| 1.7 | `session_regenerate_id(true)` en `IniciarSesion`; cookie `HttpOnly` + `SameSite=Strict` | SEC-10 |

**Esquema de la autorización (paso 1.1):**

```php
// Concepto — a implementar en valk/gatekeeper.php ANTES del call_user_func
$Politica = [
    // función                   => roles autorizados ('*' = público)
    'LoadPortada'                => ['*'],
    'LoadLogin'                  => ['*'],
    'Autentificar'               => ['*'],
    'LoadMidBlock'               => ['*'],
    'VistaAlumno'                => ['alumno'],
    'GenerarCertificado'         => ['alumno', 'docente', 'admin'],
    'LoadResultadosPaginado'     => ['docente', 'admin'],
    'CreateModifyUsuario'        => ['admin'],
    'RemoveUsuario'              => ['admin'],
    'UserVaccine'                => ['admin'],
    // … el catálogo completo está en docs/07-catalogo-endpoints.md
];

$fn     = $raw_data['valk'];
$perfil = Sesion('perfil');                       // 'alumno'|'docente'|'admin'|'0'
$roles  = isset($Politica[$fn]) ? $Politica[$fn] : null;

if ($roles === null) {                            // ⛔ denegación por defecto
    header('HTTP/1.1 403 Forbidden'); exit;
}
if (!in_array('*', $roles) && !in_array($perfil, $roles)) {
    header('HTTP/1.1 403 Forbidden'); exit;
}
// + validar token CSRF aquí para toda función que escriba (SEC-09)
call_user_func($fn, $raw_data);
```

> ⚠️ **Denegación por defecto es esencial**: toda función no listada debe rechazarse.
> Así, añadir un archivo nuevo a `gate/omega/` no expone nada por accidente.

### Fase 2 — Correcciones altas (semanas)

| # | Acción | Corrige |
|---|---|---|
| 2.1 | Escapado obligatorio de toda salida (`htmlspecialchars`, `(int)` en atributos) | SEC-08 |
| 2.2 | Token CSRF en `$.RawData` y validación en el dispatcher | SEC-09 |
| 2.3 | Forzar cambio de contraseña en el primer ingreso; reparar recuperación | SEC-06, F-07 |
| 2.4 | Migrar a `password_hash`/`password_verify` con re-hash progresivo | SEC-13 |
| 2.5 | Código de validación aleatorio por emisión + página pública de verificación | SEC-11 |
| 2.6 | Límite de tasa y mensajes de error uniformes en el login | SEC-15 |
| 2.7 | Cabeceras de seguridad + `no-store` en respuestas con datos personales | SEC-19 |
| 2.8 | Conectar la auditoría existente (`Morty`) a los eventos clave | SEC-21 |
| 2.9 | Eliminar el bypass de correo y la copia oculta fija | SEC-20 |
| 2.10 | Entregar los PDF en memoria; purga automática de `out/` | SEC-16 |

### Fase 3 — Estructural (meses)

| # | Acción | Corrige |
|---|---|---|
| 3.1 | Inventariar y actualizar dependencias; adoptar Composer; eliminar código muerto | SEC-14 |
| 3.2 | Migrar el intérprete a una versión de PHP con soporte | SEC-14 |
| 3.3 | Mover `cache/` fuera del document root; no generar `.php` en runtime | SEC-18 |
| 3.4 | Versionar el esquema y los procedimientos de la base de datos | ver MEJ-02 |
| 3.5 | Revisión de seguridad de los procedimientos almacenados (hoy no auditables) | ❓ |

---

## 6. Qué NO se pudo verificar

Este análisis es **estático y sobre el código del repositorio**. Quedan fuera:

- **Los procedimientos almacenados**, donde vive la lógica de autenticación, el hash de
  contraseñas, el conteo de intentos y toda la lógica de negocio. **No están en el repositorio.**
- La configuración real del servidor web (cabeceras, permisos, listado de directorios,
  restricciones de ejecución por directorio).
- Los permisos efectivos del usuario de base de datos.
- La versión exacta de PHP, de PHPMailer, de jQuery y de UploadHandler en producción.
- El estado actual del sitio en producción (no se realizaron pruebas dinámicas).
- Si existen controles compensatorios externos (WAF, firewall de aplicación, VPN).

**Recomendación:** complementar con (a) revisión de los procedimientos almacenados,
(b) revisión de la configuración del servidor y (c) una prueba de penetración autorizada
sobre un entorno de pruebas, una vez aplicada la Fase 1.
