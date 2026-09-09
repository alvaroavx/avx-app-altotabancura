---
doc: diagramas-de-clases-y-objetos
lectura_previa: 01-arquitectura.md
---

# 06 · Diagramas de clases y objetos

## 1. Panorama: dos jerarquías y un montón de funciones globales

El sistema **no es plenamente orientado a objetos**. Conviven tres estilos:

| Estilo | Dónde | Ejemplo |
|---|---|---|
| **Clases con herencia** | modelos del dominio y módulos del framework | `class Usuario extends Wiss` |
| **Traits como API** | comandos del framework | `trait wLogin { … }` compuesto en `Wiss` |
| **Funciones globales sueltas** | toda la capa de presentación y utilidades | `function LoadResultadosPaginado($data)` |

Esta última capa es la que el dispatcher invoca por nombre, y es la más grande del proyecto.

---

## 2. Diagrama de clases — núcleo del framework (Valk)

```mermaid
classDiagram
    class Wiss {
        -array Manifest
        -string SteinsGate
        +__construct()
        -Call(Capsule) array
        -Gate(Modo, Data) array
        +Test() array
    }
    note for Wiss "Fachada del framework.\nCompone 8 traits.\nLos modelos del dominio\nHEREDAN de ella."

    class wQuery {
        <<trait>>
        +Query(Procedure, Datos)
    }
    class wLogin {
        <<trait>>
        +ValidaLogin()
        +Recovery()
        +DatosUsuario()
        +CambiarClave()
        +ValidarToken()
        +BuscarUsuarios()
        +LinkLogin()
        +UltimosUsuarios()
    }
    class wZeus {
        <<trait>>
        +Zeus()
        +PermisosUsuario()
    }
    class wMail {
        <<trait>>
        +Mail()
        +EnviarMail()
    }
    class wChat {
        <<trait>>
        +Contactos()
        +MensajesChat()
        +AgregarChat()
    }
    class wKirito {
        <<trait>>
        +Kirito()
        +CleanCache()
    }
    class wScripts {
        <<trait>>
        +Scripts()
    }
    class wStyles {
        <<trait>>
        +Styles()
    }

    Wiss ..|> wQuery : use
    Wiss ..|> wLogin : use
    Wiss ..|> wZeus : use
    Wiss ..|> wMail : use
    Wiss ..|> wChat : use
    Wiss ..|> wKirito : use
    Wiss ..|> wScripts : use
    Wiss ..|> wStyles : use

    class Valk {
        -array Gate
        -string Modo
        -array Data
        -array Manifest
        +__construct(data)
        +Execute() array
        -LoadGate() array
        -LoadModo() array
    }
    note for Valk "Orquestador.\nModo = nombre de la clase\ndel módulo a instanciar.\nDispatch dinámico:\nnew $this->Modo(...)"

    class Steins {
        -array CoreGate
        -array Manifest
        +Gate(Gate, DbServer, MailServer) array
        +CoreQuery(Procedure, Datos) array
    }
    note for Steins "🔴 Credenciales de BD\nHARDCODEADAS por entorno\n(PRO / LOCAL)"

    class Query {
        -string Procedure
        -array Variables
        +Execute() array
    }
    class SqlServer {
        -Host
        -Nombre
        -Usuario
        -Password
        -Port
        -Procedure
        -Variables
        +Ejecutar() array
        +Error()
    }
    class PostgreSql {
        +Ejecutar() array
        +Error()
    }

    Wiss --> Valk : instancia en Call()
    Valk --> Steins : LoadGate()
    Steins --> Query : CoreQuery()
    Query --> SqlServer : si Tipo=mssql
    Query --> PostgreSql : si Tipo=postgresql
```

### Módulos de servicio (`valk/mu/v1.0/`) — interfaz implícita

Todos comparten el mismo contrato **por convención, sin `interface` declarada**:

```mermaid
classDiagram
    class ModuloValk {
        <<contrato implícito>>
        -array Manifest
        -array Gate
        -string Metodo
        -array Var
        +__construct(Manifest, Gate, Data)
        +Execute() array
    }
    note for ModuloValk "Execute() suele ser:\nif (method_exists($this, $Metodo))\n    return $this->$Metodo();\n⚠️ despacho dinámico por nombre"

    ModuloValk <|.. Login
    ModuloValk <|.. Zeus
    ModuloValk <|.. Morty
    ModuloValk <|.. Kirito
    ModuloValk <|.. Chat
    ModuloValk <|.. Mail
    ModuloValk <|.. Scripts
    ModuloValk <|.. Styles
    ModuloValk <|.. Watchdog
    ModuloValk <|.. Test
    ModuloValk <|.. Kratos
    ModuloValk <|.. Odin
    ModuloValk <|.. Query

    class Login {
        +Autentificar()
        +MultiCuenta()
        +DatosUsuario()
        +CambiarClave()
        +ValidarToken()
        +Recovery()
        +UltimosUsuarios()
        +ModificarUsuario()
        +DatosRedSocial()
        +BuscarUsuarios()
        +LinkLogin()
    }
    class Zeus {
        +PermisosUsuario()
    }
    class Morty {
        +GuardarLog()
        +BuscarLog()
        +DatosLog()
        +LoginLog()
    }
    class Kirito {
        +CleanCache()
    }
    class RedSocial {
        +LinkLogin()
        +Datos()
        +Permisos()
    }
    note for Mail "🔴 Modo BYPASS forzado: if (1 == 2)"
    note for Watchdog "❌ Execute() retorna 1, sin lógica"
    note for Kratos "❌ Esqueleto vacío"
    note for Odin "❌ Esqueleto vacío"
```

> ⚠️ **Riesgo de diseño**: `Execute()` hace `$this->$Metodo()` donde `$Metodo` proviene
> del llamador. Está acotado a métodos privados de la propia clase (`method_exists`),
> pero convierte cada módulo en un mini-dispatcher. Ver [SEC-08](08-seguridad.md).

---

## 3. Diagrama de clases — dominio de la aplicación (gate)

```mermaid
classDiagram
    class Wiss {
        <<framework>>
    }

    class Usuario {
        +Get(Busqueda, Filtros)
        +GetPaginado(Busqueda, IdUni, IdSede, IdCarrera, FDesde, FHasta, Pag, Tam, Order)
        +GetPaginadoTotal(...)
        +GetDocentes()
        +GetById(IdUsuario)
        +GetByRut(Rut)
        +GetInfoCertificadoTabla(IdUsuario)
        +GetVaccines(IdUsuario)
        +IngresarCertificado(Codigo, IdUsuario, Url)
        +CreateModify(IdUsuario, Nombres, Apellidos, Username, Rut, FNac, IdCarrera, IdSede, Password, IdTipoUsuario)
        +Remove(IdUsuario)
    }
    class Vacuna {
        +Get(IdUniversidad)
        +GetAll()
        +CreateModify(IdVacuna, Vacuna, Lote)
        +Remove(IdVacuna)
        +UserVaccine(IdUsuarioVacuna, IdVacuna, IdUsuario, Numero, Fecha)
        +DetachVaccine(IdUsuarioVacuna)
        +GetLast(IdUsuario)
    }
    class Universidad {
        +Get(IdUniversidad)
        +GetAll()
        +GetAllWithSedes()
        +CreateModify(IdUniversidad, Nombre)
        +Remove(IdUniversidad)
    }
    class Sede {
        +Get(IdUniversidad)
        +GetAll()
        +GetAllByUniversidad()
        +GetByCarrera(IdCarrera)
        +CreateModify(IdSede, IdUniversidad, Nombre)
        +Remove(IdSede)
    }
    class Carrera {
        +Get(IdUniversidad)
        +GetAll()
        +GetBySede(IdSede)
        +CreateModify(IdCarrera, Nombre)
        +Remove(IdCarrera)
    }
    class Neodoc {
        <<código muerto>>
        +Get()
        +Save()
        +Delete()
        +Restore()
        +Star()
        +getFavoritos()
    }

    Wiss <|-- Usuario
    Wiss <|-- Vacuna
    Wiss <|-- Universidad
    Wiss <|-- Sede
    Wiss <|-- Carrera
    Wiss <|-- Neodoc

    class FPDF {
        <<vendor>>
    }
    class Certificado {
        #int B
        #int I
        #int U
        #string HREF
        +Header()
        +Body(Nombre, Rut, Carrera, Sede, Universidad)
        +Vaccines(header, data)
        +Body2()
        +Signature(nroVacunas)
        +Codigo(codigo)
        +PiePagina()
        +BodyTabla()
        +VaccinesTabla()
        +UsuariosTabla(Usuario)
        +PiePaginaTabla()
        +WriteHTML(html)
        +SetStyle()
        +OpenTag()
        +CloseTag()
        +PutLink()
    }
    FPDF <|-- Certificado
```

> ⚠️ **Anomalía de herencia**: `Usuario extends Wiss` y luego invoca sus métodos como
> **estáticos**: `Wiss::Query('spSel_Usuario', $Datos)` (`cUsuario.php:15`). Como
> `wQuery::Query` es un método de **instancia** que usa `$this`, PHP 5.6 lo tolera
> (contexto `$this` heredado) pero es **sintaxis inválida en PHP 7+ en contexto estático real**
> y una fuente segura de errores en cualquier migración.
>
> 🔍 Además, la herencia es semánticamente incorrecta: `Usuario` **no es un** `Wiss`;
> lo usa. Debería ser composición.

---

## 4. Diagrama de objetos — instancia típica en tiempo de ejecución

Momento capturado: un docente pulsa "Aplicar filtros".

```mermaid
graph TB
    subgraph "Petición POST /g · valk=LoadResultadosPaginado"
        RD["<b>$raw_data</b> : array<br/>valk='LoadResultadosPaginado'<br/>busqueda='perez'<br/>iduniversidad='3'<br/>idsede='7' · idcarrera=''<br/>numeropagina='1' · tamanopagina='10'<br/>+ Manifest + Var inyectados"]
    end

    RD --> FN["<b>LoadResultadosPaginado()</b><br/>función global<br/>gate/omega/xUsuario.php:258"]
    FN --> U["<b>u : Usuario</b><br/>(hereda Manifest y SteinsGate de Wiss)"]
    U --> W["<b>capsula : array</b><br/>Modo='Query'<br/>Data.Proc='spSel_Usuario_Paginado'<br/>Data.Var={IdUsuario:'812', Busqueda:'perez', …}<br/>Manifest.IP='…' Manifest.IdUsuario='812'"]
    W --> V["<b>v : Valk</b><br/>Modo='Query'"]
    V --> S["<b>s : Steins</b><br/>CoreGate={Tipo:'mssql',<br/>Host:'sql7004.site4now.net',<br/>Nombre:'DB_A42699_altotabancura', …}"]
    V --> Q["<b>q : Query</b><br/>Procedure='spSel_Usuario_Paginado'<br/>Variables={9 claves}"]
    Q --> SS["<b>ss : SqlServer</b><br/>Params=[…] (bind)<br/>Call=' exec spSel_Usuario_Paginado<br/>@IdUsuario= ? ,@Busqueda= ? ,… '"]
    SS --> DB[("SQL Server")]
    DB -->|"array de filas asociativas"| FN
    FN -->|"echo &lt;table&gt;…&lt;/table&gt;"| OUT["Respuesta HTTP<br/>(text/html, fragmento)"]

    SES[("<b>$_SESSION</b><br/>appcertificados_idusuario='812'<br/>appcertificados_perfil='docente'<br/>appcertificados_universidad='3'<br/>appcertificados_usuario='jperez'")] -.->|"Sesion() lee"| U
    CONST[("<b>Constantes globales</b><br/>Gate, Root_Base, Root_Fisica,<br/>Entorno_Developer, Version_Valk,<br/>Prefix_*, Root_*, File_* …")] -.->|"constant() lee"| FN
    CONST -.-> S
```

**Observaciones sobre el ciclo de vida:**
- Se crea **un objeto `Valk`, uno `Steins`, uno `Query` y uno `SqlServer` por cada consulta**.
- Cada uno abre y cierra su propia conexión TCP a SQL Server ⇒ una vista con 6 selects
  abre 6 conexiones. **No hay pooling ni reutilización.**
- `Wiss::__construct()` vuelve a hacer `require` del preloader completo en cada instancia
  (`valk/Wiss.php:30`), y `encrypt_decrypt()` también (`tEncode.php:178`).
- El estado global (constantes + `$_SESSION`) es leído directamente desde cualquier capa,
  incluida la de datos ⇒ **imposible testear unitariamente**.

---

## 5. Capa de presentación: no hay clases, hay funciones

`gate/omega/x*.php` y `valk/ro/r*.php` definen **~85 funciones globales** que:
1. reciben un único parámetro `$data` (el array completo de la petición),
2. consultan modelos,
3. hacen `echo` de HTML concatenado con `.`,
4. no devuelven nada.

```mermaid
graph LR
    subgraph "gate/omega/ — un archivo por entidad"
        XC["xCore.php<br/>estructura, header, footer,<br/>portada, EsAdmin"]
        XD["xDashboard.php<br/>sidebar, filtros"]
        XU["xUsuario.php (666 líneas)<br/>vistas por rol, grillas,<br/>formularios, paginación"]
        XV["xVacuna.php<br/>catálogo + asignación de dosis"]
        XS["xSede.php"]
        XUN["xUniversidad.php"]
        XCA["xCarrera.php"]
        XA["xAdmin.php<br/>panel de administración"]
        XCE["xCertificado.php<br/>orquesta la generación de PDF"]
    end
    XC --> XU
    XD --> XUN & XS & XCA
    XU --> XV & XS & XCA
    XA --> XU & XUN & XS & XCA & XV
    XCE --> XU
```

⚠️ **Acoplamiento por funciones globales**: `xDashboard.php` llama a `LoadSelectUniversidadAll()`
definida en `xUniversidad.php`, que llama a `$.ActualizaFiltros` del cliente…
La única razón por la que funciona es que `gatekeeper.php` incluye **todos** los archivos
`x*.php` en **cada** petición (`valk/gatekeeper.php:12-31`) — lo que también significa
que **cada petición carga y parsea los 9 controladores completos**.

---

## 6. Modelo del cliente (JavaScript)

No hay clases: todo se cuelga del objeto `$` de jQuery como funciones sueltas.

```mermaid
graph TB
    subgraph "valk/js/v1.0/ — framework"
        VC["valk.core.js<br/>$.RawData $.RawCode $.Decode<br/>$.LoadModal $.BlockModal<br/>$.FormatoRut $.Uploader<br/>ajaxSetup{url:'g', type:'POST'}"]
        VL["valk.login.js<br/>$.Autentificar $.Registro<br/>$.RecoverPass $.DeleteSesion"]
        VW["valk.watchdog.js<br/>$.HeartBeat $.FootPrint<br/>$.ShadowMark $.Snitch"]
        VD["valk.debug.js<br/>$.DatosSesion"]
    end
    subgraph "gate/js/ — aplicación"
        JC["jxCore.js<br/>$.LoadCore $.LoadMidBlock<br/>$.LoadHeader $.LoadFooter"]
        JU["jxUsuario.js (719 líneas)<br/>$.VistaAlumno $.VistaDocente<br/>$.LoadResultadosPaginado<br/>$.ActualizaFiltros $.SaveUsuario<br/>$.SaveDocente $.DeleteUsuario"]
        JV["jxVacuna.js<br/>$.SaveVacuna $.AddVaccine<br/>$.SaveVaccines $.RemoveVaccine"]
        JCE["jxCertificado.js<br/>$.GenerarCertificado<br/>$.GenerarMultiplesCertificados<br/>$.GenerarCertificadoTabla"]
        JA["jxAdmin.js<br/>$.VistaAdmin $.LoadAdmin<br/>$.EditThis $.SidebarFocus"]
        JD["jxDashboard.js · jxSede.js<br/>jxUniversidad.js · jxCarrera.js"]
    end
    VC --> JC --> JU --> JV & JCE
    JA --> JD
    VW -.->|"pushState / analítica"| JC
```

**El estado del cliente vive en el DOM**, no en variables:

| Estado | Dónde se guarda |
|---|---|
| Vista actual y su id | `<div id="constructor" data-load="…" data-idload="…">` |
| Filtros de búsqueda | `<div id="filtros_data"><div data-tipo="sede" data-valor="7">…` |
| Selección de alumnos | `input[name='usuario']:checked` con `data-idusuario` |
| Modo edición de una fila | clases CSS `.view` / `.rework` alternadas |

🔍 Es un patrón deliberado (permite que el servidor regenere el HTML sin perder estado),
pero implica que **cualquier usuario puede alterar los filtros y los IDs desde las
herramientas de desarrollo** — lo que, sumado a la falta de autorización en el servidor,
es explotable (ver [SEC-03](08-seguridad.md)).
