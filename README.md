# Aplicación de Certificados — Clínica Alto Tabancura

Monolito PHP legado para emisión y administración de certificados de vacunación. La interfaz AJAX se apoya en el framework propio Valk/Steins y SQL Server mediante procedimientos almacenados.

## Guía humana

### Qué es, para qué sirve y a quiénes atiende

Es el repositorio de la **Aplicación de Certificados de Vacunación de Clínica Alto Tabancura**. Su propósito es sostener el ciclo de consulta, administración y emisión de certificados de vacunación en PDF para el contexto universitario atendido por la clínica. Lo utilizan alumnos que descargan su certificado, docentes que consultan alumnos asociados a su universidad y personal administrador que mantiene los datos.

### Qué contiene y cómo trabaja

Contiene un monolito PHP legado, el framework interno Valk/Steins, interfaz AJAX con jQuery, plantillas/estilos, librerías incorporadas manualmente y esta documentación. El navegador carga una carcasa inicial y solicita fragmentos HTML al backend; la lógica de dominio llama procedimientos almacenados de SQL Server y el flujo de emisión genera PDF en disco para su descarga. El esquema SQL y los procedimientos no forman parte del repositorio.

### Funcionalidades y tareas que resuelve

- Autenticar usuarios y diferenciar los perfiles Alumno, Docente y Administrador.
- Permitir al alumno consultar y descargar su certificado de vacunación.
- Permitir consultas de alumnos por universidad para el perfil docente.
- Administrar información de alumnos, universidades, vacunas y certificados desde el perfil administrativo.
- Generar certificados PDF y servirlos por URL.

### Trazabilidad humana

- **Solicitante:** el repositorio identifica a Clínica Alto Tabancura como producto destinatario, pero no conserva una solicitud ni una persona solicitante verificable.
- **Desarrollo:** el primer commit disponible fue creado por **AVX Informática**; la documentación técnica atribuye Valk/Steins a Alvax Informática. No permite atribuir cada cambio histórico a una persona concreta.
- **Cuándo:** el primer registro Git disponible es del **22 de noviembre de 2021**. El código documentado contiene referencias de 2018–2019, por lo que el repositorio no equivale necesariamente a la fecha de creación del sistema.

### Qué puede mejorar y oportunidades

La prioridad es reducir el riesgo antes de cualquier exposición pública: retirar secretos versionados, sustituir autenticación y despacho inseguros, revisar autorización del lado servidor y cerrar superficies de prueba/carga de archivos. El runtime ya fue adaptado a PHP 8.5; todavía conviene versionar esquema y procedimientos SQL, completar la renovación de dependencias, mejorar la observabilidad y continuar la remediación de seguridad. La hoja de ruta y la evidencia de estos riesgos están en [seguridad](docs/08-seguridad.md) y [mejoras y deuda](docs/09-mejoras-deuda-tecnica.md).

## Documentación

La guía IA First, con arquitectura, flujos, modelo de datos reconstruido, riesgos, convenciones y guía operativa, está en [docs/README.md](docs/README.md). Úsala para orientarte y verifica en código y base de datos antes de un cambio sensible.

## Estructura del repositorio

- `src/`: código fuente y raíz ejecutable de la aplicación legado.
- `data/`: respaldos locales de base de datos; los archivos `.bak` no se versionan.
- `docs/`: documentación IA First.
- `README.md` y `AGENTS.md`: puntos de entrada del repositorio.

## Inicio y estado conocido

La raíz web de la aplicación es `src/`. El runtime soportado es PHP 8.5 con el driver Microsoft `sqlsrv` 5.13; requisitos, configuración y límites de despliegue están documentados en [docs/02-stack-tecnologico.md](docs/02-stack-tecnologico.md). No hay migraciones ni esquema SQL versionados. No se debe desplegar el snapshot a internet sin revisar [seguridad](docs/08-seguridad.md).

El laboratorio Docker para recuperación de certificados se opera siguiendo [docs/entorno-local.md](docs/entorno-local.md). Es exclusivamente local y no reemplaza la infraestructura productiva.

## Pruebas automatizadas

Con el laboratorio Docker levantado, ejecute la suite de integración:

```bash
./tests/run.sh
```

La suite utiliza únicamente cuentas ficticias locales y prioriza autenticación de alumnos, generación, persistencia y descarga de certificados PDF.
