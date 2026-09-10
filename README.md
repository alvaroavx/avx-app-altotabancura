# Aplicación de Certificados — Clínica Alto Tabancura

Monolito PHP legado para emisión y administración de certificados de vacunación. La interfaz AJAX se apoya en el framework propio Valk/Steins y SQL Server mediante procedimientos almacenados.

## Documentación

La guía IA First, con arquitectura, flujos, modelo de datos reconstruido, riesgos, convenciones y guía operativa, está en [docs/README.md](docs/README.md). Úsala para orientarte y verifica en código y base de datos antes de un cambio sensible.

## Inicio y estado conocido

El runtime esperado es PHP 5.6; requisitos, configuración y límites de despliegue están documentados en [docs/02-stack-tecnologico.md](docs/02-stack-tecnologico.md). No hay migraciones, esquema SQL ni pipeline reproducible versionados. No se debe desplegar el snapshot a internet sin revisar [seguridad](docs/08-seguridad.md).
