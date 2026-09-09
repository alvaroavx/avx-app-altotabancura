---
doc: indice
proyecto: avx-app-altotabancura
nombre_producto: Aplicación de Certificados de Vacunación — Clínica Alto Tabancura
dominio_produccion: http://vacunatorioaltotabancura.cl/
stack: PHP 5.6 · framework propio "Valk/Steins" · SQL Server · jQuery
estado_documentacion: completa (generada por análisis estático del código, 2026-09-09)
fuente_de_verdad: el código del repositorio; esta documentación es derivada
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
