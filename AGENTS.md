# AGENTS.md

## Propósito

Aplicación de certificados de vacunación de Clínica Alto Tabancura. Documentación reconstruida desde el repositorio: 2026-09-10.

## Antes de trabajar

Lee `README.md`, `docs/README.md`, `docs/01-arquitectura.md`, `docs/10-convenciones-valk.md`, `docs/08-seguridad.md` y `docs/11-guia-para-agentes.md`. La documentación orienta; verifica cualquier comportamiento que vayas a modificar. El código fuente está contenido en `src/`.

## Arquitectura y rutas

- `src/index.php` / `src/valk/alphonse.php`: entrada y router.
- `src/valk/gatekeeper.php`: gate dinámico `/g`.
- `src/gate/omega/`, `src/valk/ro/`: handlers/presentación.
- `src/gate/class/`: modelos de dominio.
- `src/valk/`: framework y acceso a SQL Server mediante procedimientos.

## Restricciones críticas

- No supongas que ocultar UI autoriza una operación: revisa el recorrido completo hasta SQL Server.
- El esquema y procedures no están versionados; `data/` contiene respaldos locales no versionados. Para cambios de datos pide evidencia externa, respaldo y estrategia de despliegue.
- No expongas secretos ni datos clínicos. Los riesgos prioritarios están en `docs/08-seguridad.md`.
- Mantén las convenciones Valk y actualiza documentos afectados.
