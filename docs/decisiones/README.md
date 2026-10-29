# Registro de decisiones

| ID | Decisión | Fecha | Estado |
|---|---|---|---|
| D-01 | Adoptar PHP 8.5 como runtime soportado | 2026-10-29 | vigente |

## D-01: Adoptar PHP 8.5 como runtime soportado

**Fecha:** 2026-10-29 | **Decidió:** Álvaro Vargas | **Estado:** vigente

**Contexto:** El hosting SmarterASP ejecuta PHP 8.5.12 y la aplicación heredada contenía sintaxis y funciones incompatibles o deprecadas. Ocultar los avisos no resolvía el riesgo de fallos en login, codificación, exportación y generación de certificados.

**Alternativas descartadas:**

- Mantener PHP 8.2 — no coincide con el runtime disponible en producción y se acerca al fin de soporte de seguridad.
- Suprimir avisos de PHP — oculta síntomas sin corregir las incompatibilidades.
- Reescribir la aplicación completa — excede el objetivo urgente de recuperar y estabilizar el servicio.

**Decisión:** PHP 8.5 es el runtime soportado; el laboratorio usa PHP 8.5.12 y Microsoft Drivers for PHP for SQL Server 5.13.3, preservando los contratos heredados mientras se reemplazan APIs incompatibles.

**Consecuencias asumidas:** Se mantiene temporalmente el framework Valk y parte de sus dependencias heredadas. Todo cambio debe pasar lint en PHP 8.5 y las pruebas de login, codificación, PDF, XLSX y certificados.

**Condiciones de revisión:** Revisar al acercarse el fin de soporte de PHP 8.5, si SmarterASP cambia el runtime disponible o si una dependencia esencial exige otra versión.
