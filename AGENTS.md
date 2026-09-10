# AGENTS.md

## Propósito

Aplicación de certificados de vacunación de Clínica Alto Tabancura. Documentación reconstruida desde el repositorio: 2026-09-10.

## Antes de trabajar

Lee `README.md`, `docs/README.md`, `docs/01-arquitectura.md`, `docs/10-convenciones-valk.md`, `docs/08-seguridad.md` y `docs/11-guia-para-agentes.md`. La documentación orienta; verifica cualquier comportamiento que vayas a modificar.

## Arquitectura y rutas

- `index.php` / `valk/alphonse.php`: entrada y router.
- `valk/gatekeeper.php`: gate dinámico `/g`.
- `gate/omega/`, `valk/ro/`: handlers/presentación.
- `gate/class/`: modelos de dominio.
- `valk/`: framework y acceso a SQL Server mediante procedimientos.

## Restricciones críticas

- No supongas que ocultar UI autoriza una operación: revisa el recorrido completo hasta SQL Server.
- El esquema y procedures no están versionados; para cambios de datos pide evidencia externa, respaldo y estrategia de despliegue.
- No expongas secretos ni datos clínicos. Los riesgos prioritarios están en `docs/08-seguridad.md`.
- Mantén las convenciones Valk y actualiza documentos afectados.
