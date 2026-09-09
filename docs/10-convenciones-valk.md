---
doc: convenciones-del-framework-valk
lectura_previa: 01-arquitectura.md
proposito: permitir escribir código que encaje con el proyecto sin leer todo el framework
---

# 10 · Convenciones del framework Valk

> Valk es un framework **propietario y no documentado**. Este documento reconstruye sus
> convenciones por observación del código, para que cualquier cambio futuro sea coherente
> con el resto del sistema.

## 1. La regla maestra: **prefijo de una letra + PascalCase**

Cada tipo de archivo tiene un prefijo de una letra que **determina su carpeta, su rol y
cómo lo carga el framework**. Los prefijos se declaran en `valk/constants.php:4-21`.

| Prefijo | Constante | Carpeta | Rol | Ejemplo |
|:---:|---|---|---|---|
| `a` | `Prefix_Alfa` | `gate/alfa/` | Controlador **solo admin** (se carga únicamente si `Sesion('admin')`) | `aUsuario.php` |
| `c` | `Prefix_Class` | `gate/class/` | Clase de dominio (`class X extends Wiss`) | `cUsuario.php` |
| `d` | `Prefix_Default` | `valk/default/` | Valores por defecto de configuración | `dManifest.php` |
| `e` | `Prefix_Endpoint` | — | Máscara de URL de endpoints OAuth (`/e/…`) | — |
| `g` | `Prefix_Gate` | — | Máscara de URL del dispatcher (`/g`) | — |
| `i` | `Prefix_Res` | `res/` | Recursos estáticos | — |
| `j` | `Prefix_Js` | `gate/js/`, `valk/js/` | JavaScript | `jxUsuario.js` |
| `k` | `Prefix_Cache` | `cache/` | Máscara de URL de assets cacheados (`/k/…`) | — |
| `l` | `Prefix_Lang` | `gate/lang/` | Textos i18n | `lLogin.php` |
| `o` | `Prefix_Out` | `out/` | Máscara de URL de archivos generados (`/o/…`) | — |
| `r` | `Prefix_Ro` | `valk/ro/` | Rutinas globales del framework | `rLogin.php` |
| `s` | `Prefix_Css` | `gate/css/`, `valk/css/` | Hojas de estilo | `sxUsuario.css` |
| `t` | `Prefix_Tools` | `valk/tools/` | Funciones utilitarias globales | `tSesion.php` |
| `u` | `Prefix_Upload` | — | Máscara de URL de subidas (`/u`, sin implementar) | — |
| `v` | `Prefix_Valk` | `valk/` | Núcleo del framework | — |
| `w` | `Prefix_Commands` | `valk/commands/` | Trait de comandos compuesto en `Wiss` | `wLogin.php` |
| `x` | `Prefix_Omega` | `gate/omega/` | Controlador **público** (cualquier sesión) | `xUsuario.php` |

**Prefijos compuestos en JS y CSS**: `j`+`x` = JavaScript de un controlador *omega*
(`jxUsuario.js`); `s`+`x` = CSS de omega (`sxUsuario.css`); `j`+`a` / `s`+`a` serían los
equivalentes para *alfa* (no existen hoy). Los archivos concatenados en `cache/` siguen
la misma lógica: `jv1.0.js` = **j**avascript de **v**alk; `sx1.0.css` = **s**tyles de **o**mega.

> ⚠️ Esta convención es **estructural, no cosmética**: `valk/class.php`, `valk/gatekeeper.php`,
> `valk/scripts.php` y `valk/styles.php` construyen las rutas concatenando
> `constant('Root_X') . constant('Prefix_X') . $NombreModulo . '.php'`.
> **Un archivo mal nombrado simplemente no se carga, sin ningún error.**

## 2. El array `$Construct`: qué módulos existen

`gate/construct.php` es la **única lista de módulos** de la aplicación:

```php
$Construct = ['Admin','Carrera','Core','Dashboard','Sede','Universidad','Usuario','Vacuna','Certificado'];
```

Para cada nombre `X` de esa lista, el framework carga automáticamente, si existen:

| Archivo | Cargado por | Cuándo |
|---|---|---|
| `gate/class/cX.php` | `valk/class.php` | siempre (dentro del gatekeeper) |
| `gate/omega/xX.php` | `valk/gatekeeper.php:24` | siempre |
| `gate/alfa/aX.php` | `valk/gatekeeper.php:21` | solo si `Sesion('admin')` |
| `gate/js/jxX.js` | `valk/scripts.php` | modo desarrollo: individual; producción: concatenado |
| `gate/css/sxX.css` | `valk/styles.php` | idem |

➡️ **Añadir un módulo = añadir su nombre a este array + crear los archivos con el prefijo correcto.**

## 3. Convenciones de nombres del dominio

### 3.1 Procedimientos almacenados

`sp<Verbo>_<Entidad>[_<Variante>]`

| Verbo | Significado | Ejemplo |
|---|---|---|
| `Sel` | Consulta del gate (SELECT) | `spSel_Usuario_Paginado` |
| `Ins` | Inserción o **upsert** (`Id = 0` ⇒ alta) | `spIns_Usuario` |
| `Del` | Eliminación | `spDel_Vacuna` |
| `Rec` | Recuperación del framework (equivalente a `Sel`, usado en `valk/mu/`) | `spRec_Usuario_Autentificar` |
| `Mod` | Modificación del framework | `spMod_Usuario_Clave` |

> 🔍 `Sel` y `Rec` significan lo mismo; la diferencia es de **capa**: `Sel` lo usa el gate,
> `Rec` lo usa el framework.

### 3.2 Métodos de las clases de dominio

| Patrón | Significado |
|---|---|
| `Get(...)` | Consulta principal, generalmente filtrada |
| `GetAll()` | Catálogo completo, sin parámetros |
| `GetBy<Campo>(...)` | Consulta por un criterio (`GetByRut`, `GetBySede`) |
| `GetPaginado(...)` / `GetPaginadoTotal(...)` | Consulta paginada + su conteo |
| `CreateModify(...)` | **Upsert**: crea si el Id es `0`, si no actualiza |
| `Remove(<Id>)` | Elimina |

### 3.3 Funciones de la capa de presentación

| Patrón | Significado |
|---|---|
| `Vista<Rol>($data)` | Vista raíz de un perfil (`VistaAlumno`, `VistaDocente`, `VistaAdmin`) |
| `Load<Algo>($data)` | Renderiza un fragmento HTML en un contenedor |
| `Load<Entidad>Admin($data)` | Grilla de administración de una entidad |
| `Load<Entidad>Form($data)` | Formulario de alta |
| `<Entidad>Select($data, ...)` | Elemento `<select>` |
| `<Entidad>SelectRework($data, ...)` | `<select>` para edición en línea (clase `.rework`) |
| `CreateModify<Entidad>($data)` | Escritura (delega en el modelo) |
| `Remove<Entidad>($data)` | Borrado (delega en el modelo) |

### 3.4 Funciones del cliente

Todo se cuelga de `$`: `$.<MismoNombreQueLaFunciónPHP>` cuando hay correspondencia
directa (`$.LoadUsuario` → `LoadUsuario`), o un nombre de acción cuando la función del
cliente orquesta varias llamadas (`$.SaveUsuario`, `$.CheckClick`).

### 3.5 Clases CSS con significado funcional

| Clase | Significado |
|---|---|
| `.view` | Elemento visible en modo lectura |
| `.rework` | Elemento visible en modo edición (alternado por `$.EditThis`) |
| `.hidden` | Oculto |
| `.selected` | Filtro activo |
| `.clearable` | Filtro que `$.CleanFilters` puede limpiar |
| `.opcion_filtro` | Opción de filtro (lleva `data-tipo` y `data-valor`) |
| `.selectDisable` | Impide la selección de texto |
| `.required` | Campo obligatorio (validado en el cliente) |
| `.adminput`, `.adminbtn` | Controles del panel de administración |

## 4. Contratos de código a respetar

### 4.1 Un módulo de servicio (`valk/mu/v<versión>/X.php`)

```php
class MiModulo {
    private $Manifest;   // configuración
    private $Metodo;     // método a ejecutar
    private $Var;        // parámetros
    public function __construct($Manifest, $Gate, $Data){
        $this->Manifest = $Manifest;
        $this->Metodo   = $Data['Metodo'];
        $this->Var      = $Data['Var'];
    }
    public function Execute(){
        if (method_exists($this, $this->Metodo)) {
            LoadModulo($this->Manifest, ['Steins']);
            $m = $this->Metodo;
            return $this->$m();
        }
        return [];
    }
    private function MiOperacion(){
        $Steins = new Steins($this->Manifest);
        $Datos['Param'] = $this->Var['Param'];
        return $Steins->CoreQuery('spRec_Mi_Operacion', $Datos);
    }
}
```

Se instancia desde `Valk::LoadModo()` cuando `Modo` coincide con el nombre de la clase.

### 4.2 Un trait de comando (`valk/commands/wX.php`)

```php
trait wMiComando {
    public function MiComando($Metodo, $Datos){
        $Capsula['Metodo'] = $Metodo;
        $Capsula['Var']    = $Datos;
        return $this->Gate('MiModulo', $Capsula);   // 'MiModulo' = clase de valk/mu/
    }
    public function OperacionDeAltoNivel($x){
        $Datos['Param'] = $x;
        return $this->MiComando('MiOperacion', $Datos);
    }
}
```

⚠️ Además de crear el archivo, hay que **añadir el trait en dos lugares** de `valk/Wiss.php`:
al array `$Commands` (línea 2) y a la cláusula `use` de la clase (línea 20).

### 4.3 Una clase de dominio (`gate/class/cX.php`)

```php
class MiEntidad extends Wiss {
    public function GetAll() {
        $Datos = array();
        return Wiss::Query('spSel_MiEntidad', $Datos);
    }
    public function CreateModify($Id, $Nombre) {
        $Datos['IdMiEntidad'] = $Id;
        $Datos['Nombre']      = utf8_encode($Nombre);
        return Wiss::Query('spIns_MiEntidad', $Datos);
    }
}
```

⚠️ El uso de `Wiss::Query(...)` (sintaxis estática sobre un método de instancia) es la
convención existente **pero es incorrecta** y rompe en PHP 7+. En código nuevo, preferir
`$this->Query(...)`. Ver [`09-mejoras-deuda-tecnica.md`](09-mejoras-deuda-tecnica.md) MEJ-01.

### 4.4 Un controlador (`gate/omega/xX.php`)

```php
function MiVista($data) {
    // ⚠️ AÑADIR AQUÍ el control de acceso hasta que exista la capa central (SEC-01)
    $E = new MiEntidad();
    foreach ($E->GetAll() as $r) {
        echo '<div>' . htmlspecialchars($r['Nombre'], ENT_QUOTES, 'UTF-8') . '</div>';
        //                ↑ escapar SIEMPRE: la convención del proyecto NO lo hace (SEC-08)
    }
}
```

Regla del contrato: **un solo parámetro `$data`** (el array completo de la petición),
**salida por `echo`**, **sin `return`**.

### 4.5 Un cliente (`gate/js/jxX.js`)

```javascript
$.MiVista = function(){
    $.ajax({
        data: $.RawData("MiVista", { "param": valor }),
        success: function (response) { $("#contenedor").html(response); }
    });
};
```

`$.ajaxSetup` ya fija `url: "g"` y `type: "POST"` (`valk.core.js:4-7`): **no** hay que
repetirlos.

## 5. Utilidades globales disponibles

Cargadas siempre por `valk/tools.php`; usarlas en vez de reimplementar.

| Función | Archivo | Uso |
|---|---|---|
| `Sesion($n)` / `SetSesion($n,$v)` | `tSesion.php` | Leer/escribir sesión (⚠️ `SetSesion($n)` sin valor **borra**) |
| `ValidaSesion()` / `ValidaAdmin()` | `tSesion.php` | Comprobaciones booleanas |
| `SesionCache($n,$id)` / `SetSesionCache(...)` | `tSesion.php` | Caché por sesión |
| `IP()` | `tAcceso.php` | IP del cliente (⚠️ confía en cabeceras `X-Forwarded-For`) |
| `Redirect($url, $tipo)` | `tAcceso.php` | Genera JS de redirección (1=misma pestaña, 2=nueva) |
| `FormatearFecha($f, $formato)` | `tFormato.php` | **19 formatos** de fecha; acepta `DateTime`, array o string |
| `FormatearMoneda($m)` | `tFormato.php` | Formato de moneda chilena |
| `ArrayToXml($a)` | `tFormato.php` | Serializa a XML para pasarlo a un procedimiento (⚠️ no escapa) |
| `LimpiaMinimizador($c)` | `tFormato.php` | Minificación simple de JS/CSS |
| `Encode` / `Decode` / `encrypt_decrypt` | `tEncode.php` | base64 / rfc / url / AES-256-CBC |
| `SaltoLinea` / `LimpiaHtml` | `tEncode.php` | Normalización de saltos y entidades (⚠️ **no es escapado**) |
| `Params($array, $prefijo)` | `tCore.php` | Aplana un array a constantes globales |
| `Log2($tipo, $nombre, $datos)` | `tCore.php` | Escribe en `log.txt` (⚠️ en la raíz web) |
| `IsNull($var, $default)` | `tCore.php` | Valor por defecto |
| `Lang()` | `tCore.php` | Carga textos de `gate/lang/` |
| `LoadModulo($Manifest, [...])` | `tValk.php` | Carga módulos de `valk/mu/v<versión>/` |
| `LoadVendor([...])` | `tValk.php` | Carga una librería vía su `doorlock.php` |
| `Excel($datos, $nombre)` | `tExcel.php` | Genera XLSX en `out/` (⚠️ nunca invocada hoy) |

## 6. Constantes disponibles en tiempo de ejecución

Generadas por `Params()`; usar siempre `constant('Nombre')`.

| Familia | Ejemplos | Origen |
|---|---|---|
| Identidad | `Gate`, `Root_Base`, `Root_Fisica`, `WebServer` | `manifest.php`, `loader.php` |
| Entorno | `Entorno_Developer`, `Entorno_DBServer`, `Entorno_Error`, `Entorno_MailServer` | `manifest.php` |
| Versiones | `Version_Valk`, `Version_Js`, `Version_Css`, `Local_Js`, `Local_Css`, `Local_Favicon` | `manifest.php`, `gate/var.php` |
| Rutas | `Root_Cache`, `Root_Out`, `Root_Res`, `Root_Static`, `Root_Vendor`, `Root_Gate`, `Root_Class`, `Root_Omega`, `Root_Alfa`, `Root_Js`, `Root_Css`, `Root_Mu`, `Root_Ro`, `Root_Tools`, `Root_Commands` | `valk/constants.php` |
| Prefijos | `Prefix_Class`, `Prefix_Omega`, `Prefix_Js`, `Prefix_Cache`, `Prefix_Out`, … | `valk/constants.php` |
| Archivos | `File_Alphonse`, `File_Gatekeeper`, `File_Class`, `File_Meta`, `File_Scripts`, `File_Styles`, … | `valk/constants.php` |
| Módulos | `Modulo_Facebook`, `Modulo_Google`, `Modulo_Jodit` | `manifest.php` |
| Plugins | `Plugin_GTag`, `Plugin_FbPixel`, `GTag_Id`, `FbPixel_Id` | `dManifest.php`, `gate/keys.php` |
| Defaults | `Default_Load`, `Default_IdLoad`, `TimeZone`, `Prefix_Fisico` | `gate/var.php`, `valk/constants.php` |

> ⚠️ **`define()` no permite redefinir.** Si una constante se establece en
> `valk/default/`, cualquier definición posterior es **ignorada en silencio**.
> Este es el motivo por el que `valk/keys.php` no tiene efecto (ver [`01-arquitectura.md`](01-arquitectura.md) §6).

## 7. Errores frecuentes al trabajar en este código

| ❌ Error | ✅ Correcto |
|---|---|
| Crear `gate/omega/Usuario.php` | `gate/omega/xUsuario.php` (con prefijo) |
| Crear el archivo y olvidar `gate/construct.php` | Añadir siempre el nombre al array |
| Cambiar JS/CSS y no ver el cambio | Borrar `cache/` o subir `Version_Js` en `manifest.php` |
| Crear un trait y no registrarlo | Añadirlo en `Wiss.php` **en los dos** sitios (`$Commands` y `use`) |
| Comparar `Sesion('x') == null` | Comparar con `'0'` (es lo que devuelve) |
| `SetSesion('x', '')` para borrar | `SetSesion('x')` sin valor borra; `''` no |
| Interpolar datos en HTML sin escapar | `htmlspecialchars($v, ENT_QUOTES, 'UTF-8')` |
| Asumir que `FiltrarSesion()` detiene la ejecución | Añadir `exit` explícito |
| Confiar en `$data['idusuario']` del cliente | Usar `Sesion('idusuario')` para datos propios |
| Escribir SQL en PHP | Crear un procedimiento almacenado y llamarlo por `Wiss::Query` |
| Devolver JSON desde una función omega | La convención es `echo` de HTML (salvo que se migre, MEJ-11) |
