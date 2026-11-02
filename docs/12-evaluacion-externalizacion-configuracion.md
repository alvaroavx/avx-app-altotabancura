# Evaluación de externalización de configuración y secretos

**Proyecto:** Aplicación de Certificados Alto Tabancura
**Fecha:** 2026-11-01
**Estado del hallazgo:** 🔴 Requiere corrección de seguridad
**Alcance:** análisis estático y propuesta; no incluye implementación, rotación ni despliegue

## Resumen ejecutivo

Sí existe un problema de seguridad: la configuración productiva de SQL Server está
escrita dentro de un archivo PHP versionado. También hay material criptográfico
estático dentro del código. Esto no demuestra que las credenciales hayan sido
robadas, pero aumenta innecesariamente el número de lugares desde los cuales pueden
filtrarse: repositorio, historial Git, ZIP de despliegue, respaldos y copias locales.

Extraer **solo los secretos** es un trabajo de dificultad baja a media porque el
laboratorio ya usa variables de entorno `APP_DB_*` y SmarterASP permite definir
variables en el application pool. La ruta urgente puede completarse, con pruebas y
despliegue controlado, en aproximadamente **0,5 / 1 / 2 días hábiles**.

Extraer **toda la configuración** es un trabajo diferente y menos urgente. Valk
carga varios arreglos y los convierte en constantes globales usadas por todo el
sistema. Centralizar también rutas, prefijos, módulos, versiones y metadatos tendría
una dificultad media-alta y un esfuerzo probable total de **3 / 5 / 8 días hábiles**.
No recomiendo hacerlo en una sola intervención productiva.

## Conclusión recomendada

La solución mínima correcta es:

1. Sacar inmediatamente las credenciales de base de datos y los secretos
   criptográficos del repositorio.
2. Guardarlos como variables de entorno en SmarterASP, con nombres exclusivos para
   Alto Tabancura.
3. Mantener versionada la configuración no sensible que describe el comportamiento
   de la aplicación.
4. Rotar los secretos actuales después del cambio. Quitarlos del archivo no corrige
   las copias que ya existen en Git, respaldos o ZIP anteriores.
5. Abordar la centralización completa en una etapa posterior, solo si aporta una
   mejora operacional concreta.

## Evidencia observada

### Secretos y configuración sensible

| Área | Ubicación | Estado observado | Prioridad |
|---|---|---|---|
| SQL Server productivo | `src/valk/mu/v1.0/Steins.php` | Host, base, usuario y contraseña están definidos en código para el modo `PRO` | Crítica |
| SQL Server local | `src/valk/mu/v1.0/Steins.php` | Ya admite `APP_DB_HOST`, `APP_DB_NAME`, `APP_DB_USER`, `APP_DB_PASSWORD` y `APP_DB_PORT` | Patrón reutilizable |
| Clave e IV criptográficos | `src/valk/keys.php` | Valores estáticos, versionados y consumidos por `encrypt_decrypt()` | Alta |
| SMTP | `src/valk/mu/v1.0/Mail.php` | Espera recibir servidor, usuario y clave desde el `Gate`; el flujo debe caracterizarse antes de activarlo | Media |
| Facebook | `src/gate/var.php` | Existen campos de identificador y secreto, actualmente vacíos; el módulo está deshabilitado | Baja mientras siga inactivo |
| Analítica | `src/gate/keys.php` | Contiene identificadores de analítica; normalmente no son secretos | Baja |

No se incluyeron valores reales en este documento.

### Configuración no sensible y acoplamiento

- `src/manifest.php` define URL base, selección de entorno, servidor de datos,
  manejo de errores, correo, módulos y versiones.
- `src/gate/var.php` define títulos, rutas, archivos estáticos y valores de
  presentación.
- `src/valk/constants.php` contiene aproximadamente 63 asignaciones estructurales:
  prefijos, rutas internas y nombres de archivos del framework.
- `src/valk/preloader.php` combina configuración predeterminada, configuración del
  framework y configuración de la aplicación.
- `Params()` en `src/valk/tools/tCore.php` aplana esos arreglos y los convierte en
  constantes globales mediante `define()`.

Por esta razón, “sacar las credenciales” es acotado, pero “sacar toda la
configuración” toca el mecanismo de arranque y una gran cantidad de consumidores.

## Diseño objetivo recomendado

### 1. Secretos: variables de entorno del hosting

SmarterASP documenta que las variables se administran en **Advanced Tools → Pool
Manager → Environment Variables** y que se aplican al application pool. También
advierte que los sitios del mismo pool pueden acceder a ellas, por lo que los nombres
deben ser únicos. Fuente: [SmarterASP — How to set Environment Variable for your Account](https://www.smarterasp.net/support/kb/a2437/how-to-set-environment-variable-for-your-account.aspx).

Usaría nombres explícitos y prefijados, por ejemplo:

```text
ALTO_DB_HOST
ALTO_DB_PORT
ALTO_DB_NAME
ALTO_DB_USER
ALTO_DB_PASSWORD
ALTO_CRYPTO_KEY
ALTO_CRYPTO_IV
```

Si el pool se comparte con otros sitios, la opción más segura es un pool dedicado.
Si eso no está disponible, el prefijo exclusivo reduce colisiones, pero no elimina
la visibilidad entre aplicaciones del mismo pool.

### 2. Configuración no sensible: versionada

URLs públicas, nombres de módulos, versiones de assets, prefijos internos y rutas
relativas pueden permanecer en un archivo versionado. No son secretos y mantenerlos
junto al código permite reproducir y revisar el comportamiento de cada versión.

El repositorio ya contiene `.env.example` sin valores reales. Debe seguir siendo un
contrato de nombres y requisitos, no una fuente de credenciales.

### 3. Archivo externo: alternativa secundaria

Si se exige un archivo productivo, debería vivir **fuera de `src/` y fuera del
document root**, no estar versionado y tener permisos de lectura solo para la
identidad del application pool. Una variable como `ALTO_CONFIG_PATH` podría indicar
su ubicación.

No recomiendo colocar `.env`, `config.php` o credenciales dentro del directorio
publicado. Tampoco recomiendo guardar secretos en `.user.ini` o en un `web.config`
versionado: seguirían formando parte del paquete de despliegue y de sus copias.

## Dificultad por alternativa

| Alternativa | Dificultad | Esfuerzo opt./prob./pes. | Resultado |
|---|---|---:|---|
| A. Extraer solo SQL Server | Baja-media | 0,5 / 1 / 2 días | Elimina el riesgo principal y reutiliza el patrón local existente |
| B. Extraer todos los secretos activos | Media | 1 / 2 / 4 días | Incluye base, criptografía y eventual SMTP; requiere rotación y regresión |
| C. Externalizar toda la configuración | Media-alta | 3 / 5 / 8 días | Centraliza secretos y valores no sensibles, pero modifica el arranque de Valk |
| D. Reescribir el sistema de configuración | Alta | No recomendado en esta recuperación | Aumenta el riesgo sin mejorar inmediatamente el servicio de certificados |

Los rangos suponen una persona con conocimiento del repositorio, acceso al Pool
Manager, una ventana de despliegue y disponibilidad del laboratorio Docker. No
incluyen una migración de hosting ni una reescritura del framework.

## Plan: externalización segura de secretos

**Fecha del plan:** 2026-11-01
**Versión:** borrador
**Estado:** pendiente de aprobación por Álvaro Vargas

### Resumen ejecutivo

La primera entrega elimina credenciales y claves del código sin centralizar todavía
todas las constantes de Valk. La ruta crítica es configurar los secretos nuevos en
SmarterASP, desplegar el lector de variables y rotar las credenciales sin perder la
conexión productiva.

### Supuestos

1. El panel permite administrar variables del application pool. Si no, se debe usar
   un archivo privado fuera del webroot.
2. Existe acceso para cambiar la contraseña del usuario SQL Server. Sin rotación, el
   secreto anterior continúa siendo válido aunque se elimine del código.
3. Correo y redes sociales siguen inactivos. Si están activos, deben entrar en la
   primera entrega y el esfuerzo aumenta.
4. El laboratorio local y sus pruebas de login/certificados permanecen disponibles.
5. La estimación asume dedicación efectiva de una persona; no es una fecha comprometida.

### Fases y tareas

| # | Tarea | Entregable verificable | Depende de | Estimación opt./prob./pes. | Ruta crítica |
|---:|---|---|---|---:|:---:|
| 1 | Inventariar secretos activos | Lista sin valores y clasificación activo/inactivo | Acceso al código y panel | 1 / 2 / 4 h | Sí |
| 2 | Definir contrato de variables | Nombres, obligatoriedad y ejemplo sin secretos | Tarea 1 | 1 / 2 / 3 h | Sí |
| 3 | Leer secretos sin fallback inseguro | La aplicación falla claramente si falta una variable | Tarea 2 | 2 / 4 / 8 h | Sí |
| 4 | Configurar SmarterASP | Variables cargadas en el pool correcto | Acceso al panel | 1 / 2 / 4 h | Sí |
| 5 | Validar localmente | Login y certificados pasan la suite completa | Tarea 3 | 1 / 2 / 4 h | Sí |
| 6 | Desplegar y probar producción | Login y descarga funcionan sin errores expuestos | Tareas 4 y 5 | 1 / 2 / 4 h | Sí |
| 7 | Rotar credenciales | El secreto anterior deja de autenticar | Tarea 6 | 1 / 2 / 4 h | Sí |
| 8 | Evaluar historial y copias | Decisión documentada sobre purga o conservación | Tarea 7 | 1 / 2 / 4 h | No |

### Dependencias externas

| Dependencia | Responsable externo | Fecha límite | Plan B |
|---|---|---|---|
| Variables del application pool | SmarterASP / administrador del hosting | Antes del despliegue | Archivo privado fuera del webroot, referenciado por una variable de ruta |
| Cambio de contraseña SQL | Administrador de la base | Durante la ventana acordada | Crear un usuario nuevo, validar y retirar el anterior después |
| Pool dedicado | SmarterASP | Antes de cargar secretos si hay sitios compartidos | Variables con prefijo exclusivo y aceptación explícita del riesgo residual |

### Hitos

| Hito | Criterio de cumplimiento | Fecha probable |
|---|---|---|
| Secretos fuera del código | Ningún valor real aparece en archivos rastreados | A definir al aprobar el trabajo |
| Producción validada | Login y descarga de certificado responden correctamente | Mismo día del despliegue |
| Rotación cerrada | Credenciales anteriores revocadas | Inmediatamente después de validar producción |

### Decisiones que requiere el jefe de proyecto

1. **Alcance inicial:** elegir A) solo SQL Server, o B) todos los secretos activos.
   Recomendación: B, siempre que se confirme antes si SMTP está operativo.
2. **Application pool:** solicitar pool dedicado o aceptar que las variables sean
   visibles para los demás sitios del mismo pool. Recomendación: pool dedicado si el
   plan contratado lo permite.
3. **Historial Git:** reescribirlo o conservarlo después de rotar. Recomendación:
   rotar siempre; purgar historia solo si el repositorio o sus copias se distribuyeron
   ampliamente y se puede coordinar el cambio con todos los clones.

## Registro de riesgos

### DECISIONES REQUERIDAS

#### R-01: Credenciales productivas versionadas

- **Causa → evento → impacto:** secretos escritos en archivos rastreados → una copia
  del repositorio, ZIP o respaldo queda accesible → acceso no autorizado a la base y
  posible exposición o modificación de datos.
- **Probabilidad / impacto:** media / crítico.
- **Disparador:** cualquier acceso del repositorio o paquete por una persona o sistema
  no autorizado.
- **Opciones:** A) extraer y rotar ahora; B) aceptar explícitamente la exposición.
- **Recomendación:** opción A.
- **Decisión:** pendiente de Álvaro Vargas.

#### R-02: Variables compartidas por application pool

- **Causa → evento → impacto:** SmarterASP comparte variables dentro del pool → otro
  sitio del mismo pool puede leerlas → exposición lateral de secretos.
- **Probabilidad / impacto:** media si el pool es compartido / crítico.
- **Disparador:** confirmar que hay más de un sitio o aplicación en el mismo pool.
- **Opciones:** A) pool dedicado; B) prefijos exclusivos y aceptación del riesgo.
- **Recomendación:** A si está disponible a un costo razonable.
- **Decisión:** pendiente de Álvaro Vargas.

### Riesgos activos monitoreados

| ID | Riesgo | Prob. | Impacto | Disparador | Mitigación propuesta | Dueño |
|---|---|---|---|---|---|---|
| R-03 | Rotar la clave criptográfica invalida enlaces o cargas cifradas existentes | Alta | Mayor | Primer error de descifrado tras rotación | Ventana controlada, invalidación de sesiones y prueba de rutas cifradas | Por asignar |
| R-04 | Mantener fallback a credenciales antiguas oculta una configuración incompleta | Media | Crítico | La aplicación inicia sin variables nuevas | Fallar al arrancar con mensaje seguro y sin imprimir secretos | Por asignar |
| R-05 | Eliminar secretos solo del HEAD deja copias válidas en Git y ZIP | Alta | Crítico | Se confirma que el secreto anterior aún autentica | Rotación obligatoria; evaluar purga coordinada | Por asignar |
| R-06 | Un archivo secreto dentro del webroot puede ser descargado por mala configuración IIS | Baja-media | Crítico | Petición HTTP obtiene el archivo o una copia temporal | Variables del pool o archivo fuera del webroot | Por asignar |

## Criterios de aceptación de una futura implementación

- Ninguna credencial, clave o token real aparece en archivos rastreados.
- La aplicación no contiene valores productivos de respaldo ni fallbacks silenciosos.
- El arranque falla de forma segura cuando falta una variable obligatoria.
- El panel de SmarterASP contiene variables con prefijo exclusivo y se conoce si el
  pool es compartido.
- La contraseña SQL y cualquier secreto expuesto anteriormente fueron rotados.
- `.env` y archivos privados permanecen fuera de `src/`, de los ZIP y de Git.
- La suite local cubre login, generación y descarga de certificados.
- Producción valida PHP, conexión, login y PDF sin mostrar advertencias ni secretos.
- Logs y reportes no registran valores sensibles.

## Información necesaria antes de implementar

1. Confirmar si Alto Tabancura usa un application pool dedicado.
2. Confirmar acceso para crear variables en Pool Manager.
3. Confirmar quién puede rotar el usuario SQL Server y en qué ventana.
4. Confirmar si SMTP, Facebook o Google están realmente activos.
5. Confirmar cuántas personas o sistemas poseen clones, ZIP o respaldos del código.

Este informe no autoriza cambios ni afirma que exista un incidente de seguridad
confirmado. Sí concluye que mantener secretos productivos en código versionado es un
problema de seguridad que debe corregirse y cerrarse mediante rotación verificable.
