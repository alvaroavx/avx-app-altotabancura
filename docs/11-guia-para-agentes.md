---
doc: guia-operativa-para-agentes-de-ia
lectura_previa: README.md
audiencia: agentes de IA y personas que se incorporan al proyecto
---

# 11 · Guía operativa para agentes de IA

> Documento de arranque en frío: qué necesitas saber **antes** de tocar nada.

## 1. Contexto en 60 segundos

```yaml
proyecto:      avx-app-altotabancura
que_es:        emisión de certificados de vacunación para estudiantes universitarios
cliente:       Clínica Alto Tabancura (Vitacura, Santiago de Chile)
lenguaje:      PHP 5.6 (sin soporte de seguridad desde 2018-12-31)
framework:     "Valk" — propietario, no documentado, sin comunidad
base_datos:    SQL Server — TODA la lógica de negocio está en procedimientos almacenados
               NO versionados en este repositorio
frontend:      jQuery; el servidor devuelve fragmentos de HTML, no JSON
endpoint:      uno solo — POST /g — despacha a funciones PHP globales por nombre
datos:         SENSIBLES (salud: RUT, nombre, fecha de nacimiento, vacunas y lotes)
estado:        vulnerabilidades críticas sin corregir → leer docs/08-seguridad.md
tests:         ninguno
build:         ninguno (copiar archivos al servidor)
```

## 2. Reglas de trabajo en este repositorio

### 2.1 Antes de cambiar código

1. **Lee [`08-seguridad.md`](08-seguridad.md).** Muchas funciones son accesibles sin
   autenticación; cualquier función nueva que crees hereda ese problema.
2. **Comprueba si la lógica está en la base de datos.** Si el comportamiento depende de
   un `spXxx_...`, el código PHP **no** te dirá qué hace. No supongas: márcalo como
   desconocido y pregunta.
3. **Verifica que el archivo que vas a tocar está vivo.** Cerca del 40 % del repositorio
   es código muerto (lista en [`09-mejoras-deuda-tecnica.md`](09-mejoras-deuda-tecnica.md) MEJ-05).
   Comprueba que el módulo esté en `gate/construct.php`.
4. **Respeta la convención de prefijos.** Un archivo mal nombrado no se carga y **no
   produce ningún error**. Ver [`10-convenciones-valk.md`](10-convenciones-valk.md) §1.

### 2.2 Al escribir código

| Regla | Motivo |
|---|---|
| **Escapa toda salida** con `htmlspecialchars($v, ENT_QUOTES, 'UTF-8')` | El proyecto no lo hace; ver [SEC-08](08-seguridad.md) |
| **Fuerza `(int)`** en valores que se interpolan en atributos `onclick` | Ídem |
| **Nunca confíes en `$data['idusuario']`** para acceder a datos propios: usa `Sesion('idusuario')` | Ver [SEC-03](08-seguridad.md) |
| **Añade control de acceso explícito** al inicio de cada función nueva | Ver [SEC-01](08-seguridad.md) |
| Recuerda que **`FiltrarSesion()` no detiene la ejecución**: añade `exit` | Ver [SEC-01](08-seguridad.md) |
| Compara la sesión con **`'0'`**, no con `null` ni `false` | `Sesion()` devuelve el string `'0'` |
| **No escribas SQL en PHP**: crea un procedimiento almacenado | Convención del proyecto |
| **No introduzcas sintaxis de PHP 7+** (`??`, tipos de retorno, `match`) | El intérprete es 5.6 |
| Mantén el contrato `function X($data){ … echo …; }` | Es lo que el dispatcher espera |

### 2.3 Después de cambiar código

- **Si tocaste JS o CSS: borra `cache/`** o el cambio no se verá en producción.
- **No hay tests**: verifica manualmente el flujo afectado
  ([`05-flujos-funcionales.md`](05-flujos-funcionales.md) tiene los recorridos).
- **No hay entorno de pruebas declarado**: confirma dónde probar antes de desplegar.

## 3. Mapa "dónde está esto"

| Busco… | Está en |
|---|---|
| El flujo de login | `valk/ro/rLogin.php` |
| La generación de PDF | `gate/omega/xCertificado.php` + `gate/class/cCertificado.php` |
| La grilla de alumnos y sus filtros | `gate/omega/xUsuario.php` + `gate/js/jxUsuario.js` |
| El menú lateral y los filtros | `gate/omega/xDashboard.php` |
| El panel de administración | `gate/omega/xAdmin.php` |
| Las consultas a la base de datos | `gate/class/c*.php` (todas pasan por `Wiss::Query`) |
| Las credenciales de la base de datos | `valk/mu/v1.0/Steins.php` ⚠️ hardcodeadas |
| El enrutamiento de URLs | `valk/alphonse.php` |
| El dispatcher del endpoint `/g` | `valk/gatekeeper.php` |
| La configuración del entorno | `manifest.php` + `gate/var.php` |
| Las utilidades globales | `valk/tools/t*.php` |
| El cliente base (AJAX, modales) | `valk/js/v1.0/valk.core.js` |

## 4. Cómo verificar una afirmación sobre este sistema

```bash
# ¿Qué funciones son invocables desde /g?
grep -hoP '^function \K\w+' gate/omega/*.php valk/ro/*.php | sort

# ¿Qué llama el cliente y con qué nombre?
grep -rhoP 'RawData\(\s*[\x27"]\K[A-Za-z]+' gate/js/*.js valk/js/v1.0/*.js | sort -u

# ¿Qué procedimientos almacenados se usan?
grep -rhoP "Query\('\K[^']+" gate/class/*.php | sort -u
grep -rhoP "\\\$Procedure = '\K[^']+" valk/mu/v1.0/*.php | sort -u

# ¿Dónde se comprueba el acceso? (spoiler: casi en ningún sitio)
grep -rn "FiltrarSesion\|ValidaSesion\|ValidaAdmin\|EsAdmin" --include=*.php gate/ valk/

# ¿Qué módulos están vivos?
cat gate/construct.php

# ¿Está escapada la salida? (spoiler: no)
grep -rn "htmlspecialchars\|htmlentities" --include=*.php gate/ valk/
```

## 5. Preguntas que solo puede responder el equipo

Estas quedaron sin resolver en el análisis estático. Plantéalas antes de tomar decisiones
que dependan de ellas:

| # | Pregunta | Por qué importa |
|---|---|---|
| Q1 | ¿El sitio está actualmente en línea y accesible desde Internet? | Determina si las vulnerabilidades críticas son una exposición activa |
| Q2 | ¿Existe un respaldo del esquema y de los procedimientos almacenados? | Sin él, el sistema es irrecuperable ([MEJ-02](09-mejoras-deuda-tecnica.md)) |
| Q3 | ¿Qué algoritmo de hash usa `spRec_Usuario_Autentificar`? | Define la ruta de migración de contraseñas ([SEC-13](08-seguridad.md)) |
| Q4 | ¿Cuántos usuarios y registros de vacunación hay en producción? | Dimensiona el impacto de una brecha y el esfuerzo de migración |
| Q5 | ¿Con qué permisos se conecta el usuario de base de datos? | Determina el alcance real de un compromiso ([SEC-04](08-seguridad.md)) |
| Q6 | ¿Hay algún control externo (WAF, VPN, filtro por IP)? | Puede mitigar parcialmente [SEC-01](08-seguridad.md) |
| Q7 | ¿Existe un entorno de pruebas? | Sin él, todo cambio se prueba en producción |
| Q8 | ¿Qué versión exacta de PHPMailer y jQuery hay desplegada? | Determina la exposición a CVEs conocidos ([SEC-14](08-seguridad.md)) |
| Q9 | ¿La clínica tiene un responsable de protección de datos? | Relevante para el marco legal ([`08-seguridad.md`](08-seguridad.md) §4) |
| Q10 | ¿El sistema sigue en uso o está en desuso desde 2019? | El log se detiene en 2019-03-04; cambia radicalmente la prioridad de todo |

## 6. Errores de interpretación frecuentes

Cosas que **parecen** ser de una manera y son de otra:

| Parece | Realmente es |
|---|---|
| El payload `rawdata` está cifrado | Es **solo Base64** con separadores propietarios; legible y manipulable |
| `encrypt_decrypt()` protege el tráfico | Solo se usa en `/e/…` y en `valk/gate.php`, que **ningún cliente utiliza** |
| `FiltrarSesion()` bloquea el acceso | Solo emite un `<script>`; **la ejecución continúa** |
| `LimpiaHtml()` sanitiza la entrada | Convierte entidades **a** caracteres — va en la dirección **contraria** a un escapado |
| `gate/alfa/` protege el código de administración | La sesión `admin` casi nunca se fija (BUG-01) y la carpeta está vacía |
| El sistema de permisos (`Zeus`, `spRec_Permiso_Permiso`) funciona | Está **desconectado** por un error de indexación |
| La auditoría (`Morty`, `spIns_Log_Log`) registra los accesos | **Nada la invoca**: no hay auditoría |
| `Watchdog` monitorea errores | `Execute()` devuelve `1` sin hacer nada |
| El correo de recuperación se envía | Modo bypass forzado + credenciales SMTP inexistentes: **nunca llega** |
| `Certificado extends FPDF` sabe interpretar HTML | `WriteHTML` existe pero **todas** sus llamadas están comentadas |
| Bootstrap está en uso | Está en `vendor/` pero su carga está comentada |
| `test.php` es inofensivo | Es **accesible por HTTP** y ejecuta consultas |
| `Wiss::Query()` es una llamada estática | Es un método de **instancia** llamado con sintaxis estática; rompe en PHP 7+ |
| `$Manifest['Entorno']['BypassCorreo']` controla el bypass de correo | La constante **nunca se lee**; el bypass está fijo en el código |

## 7. Estado de esta documentación

| Documento | Qué cubre | Fuente |
|---|---|---|
| `00-contexto-negocio.md` | Qué es, para quién, reglas de negocio, glosario | código + comentarios |
| `01-arquitectura.md` | Capas, ciclo de petición, routing, configuración, caché | código |
| `02-stack-tecnologico.md` | Plataforma, dependencias, requisitos, despliegue | código |
| `03-modelo-datos.md` | ERD reconstruido, 56 procedimientos, codificación | **inferido** de firmas y columnas |
| `04-roles-y-permisos.md` | Roles, sesión, matriz de control de acceso | código |
| `05-flujos-funcionales.md` | 10 flujos con diagramas de secuencia | código |
| `06-diagramas-clases.md` | Clases, objetos, capa de presentación y cliente | código |
| `07-catalogo-endpoints.md` | Protocolo y las ~85 funciones invocables | código |
| `08-seguridad.md` | 21 hallazgos + plan de remediación en 4 fases | análisis estático |
| `09-mejoras-deuda-tecnica.md` | 15 mejoras + 18 defectos + hoja de ruta | análisis estático |
| `10-convenciones-valk.md` | Prefijos, contratos, utilidades, constantes | código |
| `11-guia-para-agentes.md` | Este documento | — |

**Cómo mantenerla:** ante cualquier cambio estructural (nuevo módulo, nuevo endpoint,
cambio en el modelo de datos o en el control de acceso), actualizar el documento
correspondiente **en el mismo cambio**. La documentación desactualizada es peor que la
ausencia de documentación, porque se confía en ella.

**Límite conocido:** todo lo relativo a la base de datos es una **reconstrucción por
ingeniería inversa**. Cuando se ejecute [MEJ-02](09-mejoras-deuda-tecnica.md) (versionar
el esquema), `03-modelo-datos.md` debe reescribirse contra el DDL real.
