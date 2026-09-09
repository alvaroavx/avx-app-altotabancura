---
doc: flujos-funcionales
lectura_previa: 04-roles-y-permisos.md
---

# 05 · Flujos funcionales (diagramas de flujo y secuencia)

Índice de flujos:

| ID | Flujo | Estado |
|---|---|---|
| [F-01](#f-01--autenticación-login) | Autenticación (login) | ✅ operativo |
| [F-02](#f-02--alumno-consulta-y-descarga-su-certificado) | Alumno: consulta y descarga de su certificado | ✅ operativo |
| [F-03](#f-03--docente-búsqueda-filtrada-de-alumnos) | Docente: búsqueda filtrada de alumnos | ✅ operativo |
| [F-04](#f-04--descarga-masiva-de-certificados) | Descarga masiva de certificados | ✅ operativo |
| [F-05](#f-05--administración-de-un-alumno-y-sus-vacunas) | Admin: alta/edición de alumno y sus vacunas | ✅ operativo |
| [F-06](#f-06--abm-de-catálogos) | Admin: ABM de catálogos | ✅ operativo |
| [F-07](#f-07--recuperación-de-contraseña-inoperante) | Recuperación de contraseña | ❌ **inoperante** |
| [F-08](#f-08--registro-de-usuario-deshabilitado) | Registro de usuario | ❌ deshabilitado |
| [F-09](#f-09--login-con-red-social-desactivado) | Login con red social | ❌ desactivado |
| [F-10](#f-10--heartbeat-de-sesión) | Heartbeat de sesión | ⚠️ parcial |

---

## F-01 · Autenticación (login)

```mermaid
sequenceDiagram
    autonumber
    actor U as Usuario
    participant P as Portada (LoadPortada)
    participant JS as valk.login.js
    participant GK as gatekeeper /g
    participant AU as Autentificar()<br/>rLogin.php:47
    participant W as Wiss→Login→Steins
    participant DB as SQL Server
    participant IS as IniciarSesion()<br/>rLogin.php:73

    U->>P: abre el sitio
    P-->>U: formulario (usuario + contraseña)
    U->>JS: submit (validado por jQuery Validate)
    JS->>GK: POST /g · valk=Autentificar<br/>usuario, pass, redsocial=1
    GK->>AU: call_user_func
    AU->>W: ValidaLogin(usuario, pass)
    W->>DB: exec spRec_Usuario_Autentificar @Usuario=?, @Clave=?
    Note over DB: ⚠️ la verificación de la contraseña<br/>y el conteo de intentos ocurren<br/>DENTRO del procedimiento (código no versionado)
    DB-->>AU: {IdUsuario, Username, IdTipoUsuario,<br/>IdUniversidad, Fails}

    alt IdUsuario = 0 (credenciales inválidas)
        AU-->>U: "Usuario o contraseña incorrecta"
        alt Fails 1–3
            AU-->>U: "Quedan N intentos"
        else Fails = 4
            AU-->>U: "Cuenta bloqueada por seguridad"
        end
    else IdUsuario > 0
        AU->>IS: IniciarSesion(IdUsuario, Username,<br/>IdTipoUsuario, IdUniversidad)
        IS->>IS: SetSesion idusuario/usuario/perfil/universidad
        IS->>W: PermisosUsuario(IdUsuario) → Zeus → spRec_Permiso_Permiso
        Note over IS: ⚠️ lee $Permisos[0]['Estado'] sobre<br/>un array indexado por IdPermiso ⇒<br/>'admin' casi nunca se fija (ver doc 04 §4)
        IS-->>U: <script>$.LoadMidBlock(2)</script>
        U->>GK: POST /g · valk=LoadMidBlock, init=2
        GK-->>U: vista según perfil
    end
```

**Puntos críticos del flujo:**

| # | Observación | Ubicación |
|---|---|---|
| 1 | La contraseña viaja en **Base64 sobre HTTP sin TLS** — equivalente a texto plano en la red | `valk.core.js:24-35`, `manifest.php:14` |
| 2 | El comentario `/* TODO: incluir validacion */` indica que la validación de entrada **nunca se implementó** | `rLogin.php:47` |
| 3 | El algoritmo de hash de contraseña es ❓ **desconocido** (vive en el SP). `RecoverPassPush` usa `md5()`, lo que sugiere MD5 sin sal | `rLogin.php:307` |
| 4 | El mensaje de bloqueo revela si la cuenta existe (enumeración de usuarios) | `rLogin.php:57-70` |
| 5 | El identificador de sesión PHP **no se regenera** tras autenticar ⇒ *session fixation* | `rLogin.php:74-103` |

---

## F-02 · Alumno: consulta y descarga su certificado

```mermaid
sequenceDiagram
    autonumber
    actor A as Alumno
    participant VA as VistaAlumno()<br/>xUsuario.php:16
    participant U as Usuario (modelo)
    participant DB as SQL Server
    participant GC as GenerarCertificado()<br/>xCertificado.php:57
    participant CC as CrearCertificado()<br/>xCertificado.php:9
    participant PDF as Certificado extends FPDF
    participant FS as out/

    A->>VA: LoadMidBlock (perfil = alumno)
    VA->>U: GetById(Sesion('idusuario'))
    U->>DB: exec spSel_Usuario_Certificado
    DB-->>VA: ficha (nombres, RUT, fecha nac., univ., sede, carrera)
    VA->>U: GetVaccines(Sesion('idusuario'))
    U->>DB: exec spSel_Usuario_Vacunas
    DB-->>VA: dosis (Dosis, Vacuna, Lote, Fecha, Observacion)
    VA-->>A: tabla de datos + tabla de vacunas + botón "Descargar certificado"

    A->>GC: click → POST /g · valk=GenerarCertificado<br/>idusuario=<b>N</b>, multiple=0
    Note over GC: 🔴 usa el idusuario ENVIADO POR EL CLIENTE,<br/>no el de la sesión → IDOR (ver SEC-03)
    GC->>CC: CrearCertificado(idusuario, $Certificado, "certificadoN")
    CC->>U: GetById + GetVaccines
    alt sin vacunas registradas
        Note over CC: bloque else vacío ⇒ se genera un PDF<br/>con solo cabecera y sin cuerpo
    else con vacunas
        CC->>CC: Codigo = md5(Rut)
        CC->>DB: exec spIns_Certificado (registra la emisión)
        CC->>PDF: Body · Vaccines · Body2 · Signature(n) · Codigo · PiePagina
    end
    GC->>FS: Output('F', out/certificadoN.pdf)
    GC-->>A: texto plano: URL http://…/o/certificadoN.pdf
    A->>FS: GET /o/certificadoN.pdf (vía alphonse)
    FS-->>A: 📄 PDF
```

**Estructura del PDF generado** (`gate/class/cCertificado.php`):

```
┌──────────────────────────────────────────┐
│  [logo Alto Tabancura]                   │  Header()
│      Certificado de Vacunación           │
├──────────────────────────────────────────┤
│ Procedimientos Clínicos Alto Tabancura,  │  Body()
│ Rut 76.004.217-K, certifica que <NOMBRE>,│
│ Rut <RUT> fue inmunizado(a) con la(s)    │
│ siguiente(s) vacuna(s):                  │
├──────────────────────────────────────────┤
│ │ Vacuna │ Lote │ Dosis │ Fecha │        │  Vaccines()
│ │  ...   │ ...  │  ...  │  ...  │        │
├──────────────────────────────────────────┤
│ Se extiende el presente certificado…     │  Body2()
│                                          │
│           [firma escaneada]              │  Signature(nº vacunas)
│                                          │  ⚠️ posición Y calculada
│      Código de validación: <md5(RUT)>    │  Codigo()
│      Fecha de emisión: <hoy>             │
│  Av. Tabancura 1515, Vitacura… teléfonos │  PiePagina()
└──────────────────────────────────────────┘
```

⚠️ **Anomalías del PDF:**
- La firma es una **imagen escaneada** (`res/signature2.png`) posicionada en coordenadas
  fijas según la cantidad de vacunas (1→6+). Con >6 dosis la tabla puede solaparse con la firma.
- El logo y la firma se cargan por **URL HTTP absoluta** (`constant('Root_Base').…`,
  `cCertificado.php:14,62`) ⇒ **el servidor hace una petición HTTP a sí mismo** por cada PDF.
  Si el sitio está caído o `allow_url_fopen` deshabilitado, la generación falla.
- Existe un intérprete HTML propio (`WriteHTML`) que quedó **todo comentado**; el PDF es texto plano.

---

## F-03 · Docente: búsqueda filtrada de alumnos

```mermaid
flowchart TD
    START([Docente autenticado]) --> VD["VistaDocente()<br/>pinta layout vacío"]
    VD --> LS["LoadSidebar()"]
    LS --> CHK{"¿EsAdmin?"}
    CHK -->|no| HID["Universidad OCULTA y fijada<br/>a Sesion('universidad')<br/>xDashboard.php:12"]
    CHK -->|sí| VIS["Universidad visible y seleccionable"]
    HID --> AUTO["⚙️ auto-ejecuta<br/>$.LoadResultadosPaginado()<br/>xDashboard.php:239"]
    VIS --> WAIT["Espera clic en 'Aplicar filtros'"]
    AUTO --> FD
    WAIT --> FD["#filtros_data:<br/>divs data-tipo/data-valor<br/>(estado del filtro en el DOM)"]
    FD --> LRP["POST /g · LoadResultadosPaginado<br/>busqueda, iduniversidad, idsede,<br/>idcarrera, fechas, pagina, tamaño, orden"]
    LRP --> SP["exec spSel_Usuario_Paginado"]
    SP --> TBL["Tabla HTML + DataTables<br/>(paging/search/order desactivados:<br/>los hace el servidor)"]
    TBL --> LP["POST /g · LoadPaginacion<br/>→ spSel_Usuario_Total"]
    LP --> PAG["Botonera de páginas"]
    TBL --> TB["LoadTopbar(): 'Seleccionar todos'<br/>+ botones de descarga (ocultos)"]
    TB --> SEL{"¿Alumnos seleccionados?"}
    SEL -->|"1–5"| B1["Muestra 'Descargar certificados'"]
    SEL -->|">5"| B2["Muestra además 'Formato tabla'"]
    B1 --> F04([→ F-04])
    B2 --> F04
```

**Interacción cruzada sede↔carrera:** al elegir una sede, el cliente recarga las
carreras de esa sede (`$.ResultadosPorSede` → `LoadSelectCarreraBySede`) y viceversa
(`$.ResultadosPorCarrera` → `LoadSelectSedeByCarrera`). ⚠️ Ambos usan un
`setTimeout(…, 500)` para recargar resultados en paralelo a la petición del filtro —
**condición de carrera**: si la BD tarda más de 500 ms, la tabla se pinta con el filtro anterior.
✅ `gate/js/jxUsuario.js` (`$.ResultadosPorSede`).

---

## F-04 · Descarga masiva de certificados

```mermaid
flowchart TD
    S([N alumnos seleccionados]) --> Q{"¿N ≤ 5?"}
    Q -->|sí| LOOP["Bucle en el CLIENTE:<br/>N peticiones GenerarCertificado<br/>(una por alumno)"]
    LOOP --> NPDF["N archivos<br/>out/certificado&lt;Id&gt;.pdf"]
    NPDF --> NDL["N descargas simuladas con<br/>&lt;a download&gt; + MouseEvent"]
    Q -->|no| MULT["1 petición GenerarCertificado<br/>usuarios=1,2,3,… multiple=1"]
    MULT --> LOOP2["Bucle en el SERVIDOR:<br/>AddPage() por alumno<br/>en un mismo objeto FPDF"]
    LOOP2 --> ONE["out/consolidado&lt;timestamp&gt;.pdf"]
    ONE --> DL1["1 descarga"]

    S --> T{"¿N > 5 y el usuario<br/>pulsa 'Formato tabla'?"}
    T -->|sí| TAB["GenerarCertificadoTabla<br/>→ spSel_Usuario_CertificadoTabla<br/>1 fila por alumno, hasta 5 dosis"]
    TAB --> ONE2["out/consolidado-tabla&lt;timestamp&gt;.pdf"]
```

⚠️ **Problemas conocidos de este flujo:**
- `$.GenerarMultiplesCertificados` lanza hasta 5 AJAX **en paralelo sin esperar**;
  el indicador de carga se apaga inmediatamente (`jxCertificado.js`).
- Los nombres `certificado<IdUsuario>.pdf` son **fijos**: dos alumnos distintos nunca
  colisionan, pero un mismo alumno **sobrescribe** su PDF anterior; y como no hay
  limpieza, `out/` **crece indefinidamente** con datos de salud.
- El PDF consolidado usa `$fecha->getTimestamp()` (resolución de 1 segundo): dos
  peticiones en el mismo segundo se **pisan** entre sí.
- `UsuariosTabla()` lee `$Usuario[$i]['Fecha']->format(...)` sin verificar `null`
  (`cCertificado.php:158-163`) ⇒ *fatal error* si el SP devuelve fecha nula.

---

## F-05 · Administración de un alumno y sus vacunas

```mermaid
sequenceDiagram
    autonumber
    actor AD as Administrador
    participant JS as jxUsuario.js
    participant GK as /g
    participant XU as xUsuario.php
    participant XV as xVacuna.php
    participant DB as SQL Server

    AD->>JS: "Nuevo Alumno" o ícono editar
    JS->>GK: LoadAlumnoForm | LoadUsuario{usuario}
    Note over XU: LoadUsuario acepta un ID **o** un RUT:<br/>if(is_numeric) GetById else GetByRut<br/>(xUsuario.php:470-480)
    GK->>XU: renderiza formulario + selects + vacunas actuales
    XU->>XV: VacunasFormularioUsuario() → GetVaccines
    XU-->>AD: formulario

    AD->>JS: modifica campos y pulsa "Guardar"
    Note over JS: valida en cliente que los .required<br/>no estén vacíos (bordes en rojo)

    alt el bloque de vacunas está abierto
        JS->>GK: N × UserVaccine{IdUsuarioVacuna, IdVacuna,<br/>IdUsuario, Numero, Fecha}
        GK->>DB: N × exec spIns_UsuarioVacuna
    end
    JS->>GK: CreateModifyUsuario{IdUsuario, Nombres, Apellidos,<br/>Username, Rut, FechaNacimiento, IdCarrera,<br/>IdSede, Password, IdTipoUsuario=1}
    GK->>DB: exec spIns_Usuario  (upsert: IdUsuario=0 ⇒ alta)
    Note over JS,DB: 🔴 SIN TRANSACCIÓN: si falla el guardado del<br/>usuario, las vacunas ya quedaron escritas
    JS->>GK: LoadUsuario{rut} + UserMessage{new|edit|save}
    GK-->>AD: formulario recargado + mensaje verde
```

**Detalles de implementación relevantes:**
- Al crear un **alumno** desde la UI, el cliente envía `Username: ""` y `Password: ""`
  (`jxUsuario.js`, `$.SaveUsuario`) ⇒ 🔍 el procedimiento almacenado debe derivar
  las credenciales desde el RUT (coherente con la regla RN-01).
- Al crear un **docente**, `IdCarrera` va fijo en `37` ("Sin Carrera") y la contraseña
  se envía **en texto plano**; si el campo vale `'******'` se interpreta como "no cambiar"
  (`jxUsuario.js`, `$.SaveDocente`).
- `$.DeleteUsuario` y `$.DeleteDocente` llaman a `RemoveUsuario` **sin diálogo de
  confirmación**: un clic accidental en el ícono de papelera borra el registro.

---

## F-06 · ABM de catálogos

Universidades, sedes, carreras y vacunas comparten el mismo patrón:

```mermaid
flowchart LR
    A["Load&lt;X&gt;Admin()"] --> B["tabla con DataTables<br/>+ inputs .rework ocultos"]
    B --> C{"acción"}
    C -->|"✏️ EditThis(elem)"| D["alterna clases:<br/>.view ↔ .rework<br/>(edición inline, sin AJAX)"]
    D --> E["SaveX() → CreateModifyX<br/>→ spIns_X (upsert)"]
    C -->|"➕ Load&lt;X&gt;Form()"| F["formulario de alta<br/>Id&lt;X&gt; = 0"]
    F --> E
    C -->|"🗑️ DeleteX()"| G{"¿Cantidad == 0?"}
    G -->|"sí: el ícono existe"| H["RemoveX → spDel_X"]
    G -->|"no: el ícono no se pinta"| I["🚫 no eliminable"]
    E --> A
    H --> A
```

⚠️ La regla "solo se elimina si `Cantidad == 0`" se aplica **únicamente ocultando el
ícono en el HTML** (`xUniversidad.php:76`, `xSede.php:151`, `xCarrera.php:83`, `xVacuna.php:45`).
La función `RemoveX` no la revalida ⇒ una petición directa a `/g` puede borrar un
catálogo en uso. ❓ A menos que el procedimiento almacenado lo impida (no verificable).

---

## F-07 · Recuperación de contraseña (INOPERANTE)

```mermaid
flowchart TD
    A["LoadRecoverPass()<br/>formulario de correo"] --> B["RecoverPass()"]
    B --> C["spRec_Usuario_Recuperar → Token"]
    C --> D["Wiss→EnviarMail(...)"]
    D --> E["Mail.php: lee<br/>$Gate['ServidorCorreo']<br/>$Gate['UsuarioCorreo']<br/>$Gate['ClaveCorreo']"]
    E --> F["🔴 Steins::Gate() SOLO devuelve<br/>Tipo/Host/Nombre/Usuario/Clave/Port<br/>de la BASE DE DATOS"]
    F --> G["⇒ índices inexistentes<br/>⇒ SMTP sin host/credenciales"]
    D --> H["🔴 Además: if(1 == 2) …<br/>Mail.php:47 fuerza el modo BYPASS"]
    H --> I["Todo correo se envía a<br/>soporte@steins.cl con asunto 'BYPASS:'"]
    G --> J(["❌ El usuario NUNCA recibe el enlace"])
    I --> J

    K["Aunque llegara:<br/>/recover/&lt;token&gt;"] --> L["🔴 'recover' NO está en<br/>gate/shortcut.php ⇒ la URL<br/>cae al dashboard por defecto"]
    L --> J

    style J fill:#ffcdd2
```

✅ Verificado: `valk/mu/v1.0/Mail.php:44` (`if(1 == 2)`), `Mail.php:16-18` vs
`valk/mu/v1.0/Steins.php:25-27`, `gate/shortcut.php` (sin `recover`),
`valk/ro/rLogin.php:12` (el enlace "Recuperar Contraseña" está **comentado** en el formulario).

**Impacto operativo:** un usuario que olvide su contraseña **no tiene forma autónoma
de recuperarla**. La única vía es que un administrador la reasigne. Además,
`RecoverPassPush` aplica `md5()` a la nueva contraseña (`rLogin.php:307`) mientras que
`Autentificar` envía la contraseña **sin hashear** (`rLogin.php:50`) —
un cambio de contraseña por esta vía dejaría al usuario **sin poder entrar**. 🔍

---

## F-08 · Registro de usuario (DESHABILITADO)

`LoadRegistro()` (`rLogin.php:107`) pinta un formulario completo con validaciones y
términos y condiciones, pero:
- el botón "Registrarse" del login está **comentado** (`rLogin.php:16`);
- la función servidor `Registrar()` (`rLogin.php:176-183`) **asigna variables locales y no hace nada más** — cuerpo sin implementar;
- el texto de términos y condiciones es un *placeholder* en inglés que menciona
  `[forum-name]` y Facebook Login (`valk/ro/rValk.php:3`).

⚠️ Aun así, `Registrar` y `LoadRegistro` **siguen siendo invocables** desde `/g`.

---

## F-09 · Login con red social (DESACTIVADO)

Infraestructura completa (SDK de Facebook, `RedSocial.php`, `valk/endpoint.php`,
ruta `/e/<payload-AES>`, procedimiento `spRec_Usuario_MultiCuenta`) pero:
- `Modulo_Facebook = 0` y `Modulo_Google = 0` (`manifest.php:28-30`);
- `$Var['Facebook']['Id']` y `['Secret']` están **vacíos** (`gate/var.php:43-46`);
- Google nunca se implementó (`$Link['Google'] = 'PENDIENTE'`, `RedSocial.php:54`);
- `vendor/Facebook/doorlock.php` está **corrupto** (bytes binarios desde la línea ~60);
- `vendor/Google/` no tiene `doorlock.php`, solo un `autoload.php.old`.

⇒ Si alguien activara estos flags, la aplicación **fallaría al cargar**.

---

## F-10 · Heartbeat de sesión

```mermaid
sequenceDiagram
    participant U as Navegador
    participant EB as EstructuraBase()
    participant HB as HeartBeat()<br/>rLogin.php:321

    EB-->>U: <script>$.HeartBeat();</script>
    U->>HB: POST /g · valk=HeartBeat, edward=0
    alt sesión inválida
        HB-->>U: RemoveSesion() → session_destroy()<br/>+ clearTimeout(HeartBeat) + $.LoadCore()
    else sesión válida
        HB-->>U: (respuesta vacía)
    end
    Note over U,HB: ⚠️ El bucle de reintento (setTimeout cada 5 s)<br/>está COMENTADO en valk.watchdog.js ⇒<br/>el heartbeat se ejecuta UNA SOLA VEZ
```

⚠️ Sin bucle activo, el heartbeat no detecta expiraciones posteriores de sesión:
el usuario descubre que su sesión murió solo cuando una acción devuelve el script de login.
