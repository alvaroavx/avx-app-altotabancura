---
doc: indice
proyecto: avx-app-altotabancura
nombre_producto: Aplicación de Certificados de Vacunación — Clínica Alto Tabancura
dominio_produccion: http://vacunatorioaltotabancura.cl/
stack: PHP 5.6 · framework propio "Valk/Steins" · SQL Server · jQuery
estado_documentacion: completa (generada por análisis estático del código, 2026-09-09)
fuente_de_verdad: el código del repositorio; esta documentación es derivada
alcance: evidencia estática; no certifica operación, base de datos ni producción
---

# Documentación IA-First — avx-app-altotabancura

> **Propósito de esta carpeta:** permitir que un agente de IA (o una persona nueva)
> entienda, opere y modifique este sistema **sin leer todo el código fuente**.
> Cada documento declara explícitamente qué es **hecho verificado en el código**,
> qué es **inferencia** y qué es **desconocido**.

## Cómo leer esta documentación

| Si tu objetivo es… | Empieza por |
|---|---|
| Entender qué hace el sistema y para quién | [`00-contexto-negocio.md`](00-contexto-negocio.md) |
| Entender cómo está construido | [`01-arquitectura.md`](01-arquitectura.md) |
| Saber qué tecnologías/dependencias hay | [`02-stack-tecnologico.md`](02-stack-tecnologico.md) |
| Trabajar con la base de datos | [`03-modelo-datos.md`](03-modelo-datos.md) |
| Entender permisos y quién ve qué | [`04-roles-y-permisos.md`](04-roles-y-permisos.md) |
| Seguir un caso de uso extremo a extremo | [`05-flujos-funcionales.md`](05-flujos-funcionales.md) |
| Ver clases, objetos y relaciones | [`06-diagramas-clases.md`](06-diagramas-clases.md) |
| Invocar o modificar una función del backend | [`07-catalogo-endpoints.md`](07-catalogo-endpoints.md) |
| Evaluar riesgos antes de exponer el sistema | [`08-seguridad.md`](08-seguridad.md) ⚠️ **leer primero antes de desplegar** |
| Planificar refactors o mejoras | [`09-mejoras-deuda-tecnica.md`](09-mejoras-deuda-tecnica.md) |
| Escribir código que "encaje" en el proyecto | [`10-convenciones-valk.md`](10-convenciones-valk.md) |
| Operar sobre el repo siendo un agente de IA | [`11-guia-para-agentes.md`](11-guia-para-agentes.md) |

## Resumen ejecutivo en 10 líneas

1. Es una **aplicación web de emisión de certificados de vacunación** para estudiantes universitarios vacunados en la Clínica Alto Tabancura (Vitacura, Santiago de Chile).
2. Tres perfiles: **Alumno** (descarga su certificado), **Docente** (consulta alumnos de su universidad), **Administrador** (CRUD completo).
3. Está construida sobre un **framework propietario no documentado** llamado **Valk** (capa de aplicación) + **Steins** (capa de datos), de autoría de Alvax Informática.
4. **Toda la lógica de datos vive en procedimientos almacenados de SQL Server**; el PHP no escribe SQL.
5. Es una **SPA rudimentaria**: `index.php` pinta un cascarón HTML vacío y todo el contenido llega por AJAX como **fragmentos de HTML** (no JSON).
6. Existe **un solo endpoint HTTP real** (`/g`) que hace *dispatch dinámico* a funciones PHP globales por nombre.
7. La salida final del negocio son **PDFs generados con FPDF** escritos a disco en `out/` y servidos por URL.
8. El código data de **2018–2019**; el repositorio se publicó como *"First upload"* en **2021-11-22** (sin historia previa).
9. **Existen vulnerabilidades críticas** (ejecución remota de código, acceso no autenticado a datos de salud, credenciales en el repositorio). Ver [`08-seguridad.md`](08-seguridad.md).
10. No hay tests, ni gestor de dependencias, ni pipeline de despliegue, ni migraciones de BD.

## Mapa de archivos del repositorio

```
avx-app-altotabancura/
├── index.php              # Único punto de entrada HTML (cascarón SPA)
├── manifest.php           # Configuración del entorno (URLs, flags, versiones)
├── test.php               # Script de pruebas manual (código muerto, expuesto)
├── .htaccess / web.config # Reescritura: todo → index.php (Apache / IIS)
├── log.txt                # Log de texto plano escrito por Log2()
├── cache/                 # Artefactos JS/CSS concatenados generados en runtime
├── gate/                  # ← LA APLICACIÓN (lógica específica del negocio)
│   ├── construct.php      #   Lista de módulos a cargar
│   ├── shortcut.php       #   Rutas amigables permitidas
│   ├── var.php keys.php meta.php  # Config del "gate"
│   ├── class/  (cXxx.php) #   Modelos de dominio → llaman procedimientos almacenados
│   ├── omega/  (xXxx.php) #   Controladores/vistas públicos (renderizan HTML)
│   ├── alfa/   (aXxx.php) #   Controladores solo-admin (prácticamente vacío)
│   ├── js/     (jxXxx.js) #   Cliente jQuery por módulo
│   ├── css/    (sxXxx.css)#   Estilos por módulo
│   └── lang/   (lXxx.php) #   Textos i18n (sin uso efectivo)
├── valk/                  # ← EL FRAMEWORK (reutilizable entre proyectos)
│   ├── loader.php         #   Bootstrap
│   ├── alphonse.php       #   Router / servidor de assets
│   ├── gatekeeper.php     #   Dispatcher del endpoint /g
│   ├── Wiss.php cValk.php #   Fachada de servicios y orquestador
│   ├── commands/ (wXxx)   #   Traits: API de alto nivel para el gate
│   ├── mu/v1.0/           #   Módulos de servicio (Steins, Query, Login, Mail…)
│   ├── ro/     (rXxx.php) #   Rutinas globales (login, uploader, debug)
│   ├── tools/  (tXxx.php) #   Funciones utilitarias globales
│   ├── default/(dXxx.php) #   Valores por defecto de configuración
│   ├── js/v1.0/ css/v1.0/ #   Cliente del framework
├── vendor/                # Librerías de terceros copiadas a mano (sin Composer)
│   ├── FPDF/ PHPMailer/ Facebook/ Google/ Jodit/
├── res/ img/              # Recursos estáticos (íconos, logo, firma digitalizada)
└── docs/                  # ← esta documentación
```

## Convención de confianza usada en estos documentos

| Marca | Significado |
|---|---|
| ✅ **Verificado** | Leído directamente en el código, con referencia `archivo:línea`. |
| 🔍 **Inferido** | Deducido de nombres, parámetros o uso; no verificable sin acceso a la BD o al servidor. |
| ❓ **Desconocido** | Requiere acceso a la base de datos de producción, al servidor o a la persona autora. |

Una recomendación, diseño objetivo o plan de remediación no significa que ya esté implementado.

## Estado de evidencia y límites

| Área | Evidencia disponible | Límite importante |
|---|---|---|
| Aplicación | Lectura estática de PHP, JavaScript, configuración y dependencias versionadas | No prueba ejecución bajo PHP 5.6 ni comportamiento en servidor real |
| Persistencia | Nombres, parámetros y resultados consumidos de procedimientos | No hay DDL, migraciones, tablas ni procedures versionados |
| Seguridad | Superficie y controles observables en el repositorio | No hubo pentest, escaneo dinámico ni verificación de configuración externa |
| Operación | Requisitos y runbook propuestos desde el código | No hay despliegue reproducible, CI ni evidencia de producción |
| Datos clínicos | Flujos y riesgos documentados | Nunca incluir datos reales, credenciales, tokens ni contenido de usuarios |

## Rutas de lectura según la tarea

| Si necesitas… | Lee primero | Continúa con |
|---|---|---|
| Entender propósito, actores y reglas de negocio | [00 · Contexto de negocio](00-contexto-negocio.md) | [05 · Flujos funcionales](05-flujos-funcionales.md) |
| Reconstruir una petición, ruta o acción AJAX | [01 · Arquitectura](01-arquitectura.md) | [07 · Catálogo de endpoints](07-catalogo-endpoints.md), [10 · Convenciones Valk](10-convenciones-valk.md) |
| Trabajar con entidades, resultados o procedimientos | [03 · Modelo de datos](03-modelo-datos.md) | [06 · Diagramas de clases](06-diagramas-clases.md) |
| Analizar qué puede hacer cada perfil | [04 · Roles y permisos](04-roles-y-permisos.md) | [08 · Seguridad](08-seguridad.md) antes de confiar en la UI |
| Modificar una funcionalidad heredada | [11 · Guía para agentes](11-guia-para-agentes.md) | [10 · Convenciones Valk](10-convenciones-valk.md), después el flujo afectado |
| Evaluar un despliegue, exposición o incidente | [08 · Seguridad](08-seguridad.md) | [02 · Stack tecnológico](02-stack-tecnologico.md), [09 · Mejoras y deuda](09-mejoras-deuda-tecnica.md) |

## Índice detallado

| Documento | Pregunta que responde |
|---|---|
| [00 · Contexto de negocio](00-contexto-negocio.md) | ¿Qué problema resuelve, quiénes participan y cuáles son las reglas observables? |
| [01 · Arquitectura](01-arquitectura.md) | ¿Cómo viaja una petición entre navegador, Valk, dominio, archivos y SQL Server? |
| [02 · Stack tecnológico](02-stack-tecnologico.md) | ¿Qué runtime, librerías, configuración y requisitos operativos se observan? |
| [03 · Modelo de datos](03-modelo-datos.md) | ¿Qué modelo conceptual y contratos de procedimientos se pueden reconstruir? |
| [04 · Roles y permisos](04-roles-y-permisos.md) | ¿Qué roles muestra la UI y dónde faltan controles? |
| [05 · Flujos funcionales](05-flujos-funcionales.md) | ¿Cómo funcionan login, certificados, búsquedas, ABM y recuperación? |
| [06 · Diagramas de clases](06-diagramas-clases.md) | ¿Qué responsabilidades tienen Valk, Wiss, modelos y cliente? |
| [07 · Catálogo de endpoints](07-catalogo-endpoints.md) | ¿Qué rutas y funciones son invocables y cómo se codifica `rawdata`? |
| [08 · Seguridad](08-seguridad.md) | ¿Qué hallazgos requieren contención y qué no puede verificarse estáticamente? |
| [09 · Mejoras y deuda técnica](09-mejoras-deuda-tecnica.md) | ¿Qué priorizar para recuperar seguridad, operación y mantenibilidad? |
| [10 · Convenciones Valk](10-convenciones-valk.md) | ¿Cómo componer módulos sin romper contratos internos? |
| [11 · Guía para agentes](11-guia-para-agentes.md) | ¿Cómo investigar, cambiar y validar con límites explícitos? |

## Antes de intervenir o desplegar

1. Lea [08 · Seguridad](08-seguridad.md); sus hallazgos son riesgos a confirmar y contener, no cambios ya realizados.
2. Trace cliente → `/g` → handler → modelo → procedimiento almacenado. La visibilidad de la UI no demuestra autorización servidor a servidor.
3. Para cambios de datos, obtenga DDL, procedimientos, respaldo probado y estrategia de rollback/forward-fix: el repositorio no basta.
4. No copie secretos ni datos clínicos en documentos, tickets, logs o respuestas; externalice y rote secretos mediante un plan coordinado.
5. Valide en ambiente aislado: runtime objetivo, flujos por rol, autorización horizontal, sesión, XSS/CSRF/upload y restauración.

## Mapa de ejecución observable

```text
index.php / rutas amigables
  -> valk/alphonse.php (ruteo y assets)
  -> valk/gatekeeper.php (/g, despacho dinámico)
  -> gate/omega o valk/ro (presentación y acciones)
  -> gate/class -> Wiss/Valk/Steins (servicios y datos)
  -> SQL Server (procedimientos almacenados no versionados)
```

## Mantenimiento de este índice

- Actualice el índice si cambia la topología documental; conserve enlaces relativos y Mermaid compatible.
- Diferencie evidencia estática, validación local, integración real y producción.
- No convierta hipótesis, configuración versionada o recomendaciones en hechos operativos.
- La documentación es una guía navegable; la fuente de verdad sigue siendo el comportamiento efectivo y sus dependencias externas.
