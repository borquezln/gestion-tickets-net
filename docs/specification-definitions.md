# Decisiones de Fase 1

> Antes de iniciar las decisiones solicitadas por el agente, se suman una definiciones importantes que afectan a este documento.
>
> Los tickets pueden ser de tipo Incidente (problema específico reportado por un área) o Tarea (problema preexistente o mejora a realizar en alguna área).
>
> Se reemplaza la palabra *tarea* por *ticket* cuando sea necesario para evitar ambigüedades.
>
> Todo el modelo debe pasar a inglés (por ejemplo, *fechaProblema* -> *problem_date*).
> 
> Como regla general, ninguna regla de negocio del legado es válida sin una aprobación explícita.
>
> El usuario *Administrador* original en español, en inglés pasará a ser *Superuser* para no generar confusiones con *Administrative*.

---

## Resumen de la primera ronda de decisiones

| ID | Tema | Prioridad |
|---|---|---|
| D-01 | Modelado de eliminación | P0 |
| D-02 | Catálogo definitivo de roles | P0 |
| D-03 | Alcance de visibilidad | P0 |auditoría. |
| D-04 | Semántica de área/dirección | P0 |
| D-05 | Reglas de asignación | P0 |
| D-06 | Baja/cambio de área del responsable | P1 |
| D-07 | Autenticación y sesiones | P0 |
| D-08 | Poblaciones de estadísticas | P1 |
| D-09 | Nota Electrónica | P1 |
| D-10 | Fecha declarada del problema | P1 |
| D-11 | Comprobante obligatorio/tipo/tamaño | P1 |
| D-12 | Permisos del Agente | P1 |
| D-13 | Edición de tickets cerrados | P1 |
| D-14 | Zona horaria exacta | P1 |
| D-15 | Inmutabilidad del legajo | P1 |
| D-16 | Obligatoriedad del correo | P1 |
| D-17 | Roles dinámicos vs fijos | P1 |
| D-18 | Baja lógica de áreas | P2 |
| D-19 | Cambio de código de dirección | P2 |
| D-20 | Modelo de identidad | P1 |
| D-21 | Multi-área / multi-rol | P2 |
| D-22 | Alcance y retención de auditoría | P1 |
| D-23 | Política de contraseñas y bloqueo | P2 |
| D-24 | Exportación | P2 |
| D-25 | Ciclo de vida de comprobantes | P2 |
| D-26 | Número visible de ticket | P2 |
| D-27 | Concurrencia e idempotencia | P2 |
| D-28 | Datos iniciales | P1 |

---

### Decisiones P0

#### D-01 — Modelado de la eliminación

- **Contexto:** Q-016 permite eliminar desde cualquier estado y restaurar conservando el estado previo. El legado tiene un estado `Eliminado` y no permite restaurar.
- **Problema:** ¿La eliminación es una dimensión separada del ciclo de vida o un quinto estado?
- **Decisión:** Cuatro estados + marca de eliminación. Restaurar, limpia la marca y conserva el estado.

#### D-02 — Catálogo definitivo de roles

- **Contexto:** Q-003 confirma Administrador y Administrativo; prevé Agente y Responsable de área. El análisis no define sus permisos.
- **Problema:** ¿Qué roles existen en la primera versión?
- **Decisión:** Administrador, Administrativo, Responsable de área y Agente.

#### D-03 — Alcance de visibilidad por rol y área

- **Contexto:** Q-005 deja la visibilidad a definir. El prompt exige no asumir acceso global y considerar varias áreas operativas.
- **Problema:** ¿Qué datos ve cada rol?
- **Decisión:** Administrador acceso global; Administrativo y Responsable, por área; Agente, sólo tickets asignados y sin asignar de su área.

#### D-04 — Semántica de “área operativa”, “área” y “dirección”

- **Contexto:** `definitions.md` habla de “área operativa” (mantenimiento, sistemas) y Q-004 dice que “un área debe ser una dependencia de una dirección”. El legado usa “Área encargada” y “Dirección de la persona afectada”.
- **Problema:** ¿“Área operativa” es la misma entidad que “Área dependiente de una dirección” o son conceptos distintos? ¿La dirección del ticket es la del área o la del afectado?
- **Decisión:**
  * Una dirección de un conjunto de áreas.
  * En el modelado, cada Área debe tener un direccion_id. "Área operativa” es la misma entidad que “Área dependiente de una dirección”.

#### D-05 — Reglas de asignación

- **Contexto:** Q-015 crea la asignación como funcionalidad nueva. No define quién asigna, a quién, ni si iniciar exige responsable.
- **Problema:** ¿Cómo se asigna y qué relación tiene con el inicio/completado?
- **Decisión:**
  * Administrador tiene permisos globales; Administrativo puede crear, editar creadas propias y asignar; Responsable puede crear, editar, asignar, reasignar, cancelar, eliminar; Agente puede tomar tickets sin asignar.
  * El responsable debe estar activo, pertenecer al área y tener rol Agente o Responsable.
  * Iniciar no exige responsable; un usuario autorizado puede asignar antes de iniciar.

#### D-07 — Autenticación y sesiones

- **Contexto:** Q-012 exige expiración deslizante centralizada, pero no define duración. El frontend es React independiente. Q-001 deja “invalidar sesiones y/o contraseña”.
- **Problema:** ¿Cookie de servidor o tokens? ¿Duración deslizante/absoluta? ¿Qué eventos invalidan sesiones?
- **Decisión:** Cookie de sesión `HttpOnly` + sesión de servidor; 60 min deslizantes; invalidar al dar de baja, cambiar rol/área o blanquear contraseña; exigir nuevo blanqueo al reactivar.

---

## Decisiones P1

#### D-06 — Baja o cambio de área de un responsable con tickets activos

- **Contexto:** Un usuario puede tener tickets asignados. Si se da de baja o cambia de área, los tickets pueden quedar sin responsable accesible.
- **Problema:** ¿Se bloquea la operación, se desasigna o se reasigna automáticamente?
- **Decisión:** Bloquear la baja/cambio con aviso explícito hasta reasignar o desasignar.

#### D-08 — Poblaciones exactas de estadísticas

- **Contexto:** Q-020 define fechas base, pero no si las completadas luego eliminadas cuentan, ni si “eliminadas” son eventos o tickets actualmente eliminados, ni si el área es la actual o la histórica.
- **Problema:** Un KPI puede dar valores distintos según la población.
- **Decisión:** Reportes históricos por evento con filtro explícito; panel operativo excluye eliminadas y las muestra aparte; documentar el área actual.

#### D-09 — Nota Electrónica

- **Contexto:** Q-007 dice que es un documento externo y que se persiste su identificador. El legado la trataba como texto libre opcional y la perdía al editar.
- **Problema:** ¿Qué formato tiene el identificador? ¿Es obligatorio? ¿Quién lo ve? ¿Reemplaza al checkbox “Con Nota Electrónica”?
- **Decisión:** Identificador externo opcional, con formato/longitud configurables; visible para roles operativos; sin checkbox separado.

#### D-10 — Fecha declarada del problema

- **Contexto:** Q-023 dice que `fechaProblema` es la fecha declarada del problema y es editable; `fechaCreada` es el timestamp del dato. El legado usaba `NOW()` y no la editaba.
- **Problema:** ¿Es fecha y hora o sólo fecha? ¿Se puede editar siempre? ¿Puede ser futura?
- **Decisión:** Fecha y hora, editable en `Pendiente`/`EnProgreso`, con valor inicial configurable; pueden haber tareas asignadas a futuro, no incidentes.

#### D-11 — Comprobante obligatorio, tipo y tamaño

- **Contexto:** BR-028 y Q-019 indican un archivo por ticket y validación de tipo/tamaño; el legado sólo validaba PDF en el cliente.
- **Problema:** ¿Es siempre obligatorio? ¿PDF únicamente? ¿Qué tamaño máximo?
- **Decisión:** PDF obligatorio para completar; máximo configurable; validación en servidor.

#### D-12 — Permisos del Agente

- **Contexto:** El rol Agente es nuevo y Q-015 no define sus permisos.
- **Problema:** ¿Puede crear, editar o cancelar tickets, además de ejecutar las asignadas?
- **Decisión:** Agente puede tomar tickets sin asignar y transicionar los propios (iniciar, completar, cancelar).

#### D-13 — Edición de tickets completados o cancelados

- **Contexto:** BR-031 permite editar sólo Pendiente/En Progreso. Q-016 permite eliminar desde cualquier estado.
- **Problema:** ¿Se puede corregir una ticket cerrado sin reabrirla?
- **Decisión:** Las tickets cerrados son inmutables en ese estado. Se pueden reabrir (si el ticket recibe quejas) o eliminar (y restaurar posteriormente).

#### D-14 — Zona horaria exacta

- **Contexto:** Q-026 exige zona local fija; no indica cuál.
- **Problema:** ¿Qué zona se usa para “hoy”, rangos y visualización?
- **Decisión:** `America/Argentina/Buenos_Aires` por defecto, configurable para Administrador.

#### D-15 — Inmutabilidad del legajo

- **Contexto:** Q-014 usa el legajo como vínculo. El legado permitía editarlo porque era PK.
- **Problema:** ¿Puede cambiar el legajo de un usuario?
- **Decisión:** Por regla de negocio, es inmutable. Si hay un error, se corrige con un procedimiento administrativo auditado excepcional.

#### D-16 — Obligatoriedad del correo

- **Contexto:** Q-013 exige unicidad de usuario y correo; el legado permitía correo vacío. El login acepta correo.
- **Problema:** ¿El correo es obligatorio?
- **Decisión:** Obligatorio. Por regla de negocio debería ser interno de la organización.

#### D-17 — Roles dinámicos vs catálogo fijo

- **Contexto:** Q-003/Q-024 permiten cambiar roles; no definen si el catálogo de roles/permisos se edita en runtime.
- **Problema:** ¿El Administrador puede crear roles y asignar permisos, o el catálogo es fijo?
- **Decisión:** Catálogo fijo de roles con permisos declarativos; `P-ROL-GESTIONAR` queda reservado.

#### D-20 — Modelo de identidad

- **Contexto:** Q-014 pide usar el legajo como vínculo; el legado usaba el legajo como PK y también concatenaba nombres.
- **Problema:** ¿La FK de tickets apunta al legajo o a un id subrogado?
- **Decisión:** Los tickets referencian el id y muestran/buscan por legajo.

#### D-22 — Alcance y retención de auditoría

- **Contexto:** Q-017 exige historial y auditoría de cambios; no define qué se audita ni por cuánto tiempo.
- **Problema:** ¿Se auditan sólo cambios de negocio o también lecturas/descargas? ¿Cuánto se retiene?
- **Decisión:** Se auditan sólo cambios de negocio. En principio, se retiene todo.

#### D-28 — Datos iniciales

- **Contexto:** Q-021 indica inicio desde cero; Q-003 permite reiniciar índices.
- **Problema:** ¿Cómo se crea el primer Administrador y los roles/permisos?
- **Decisión:** Seed de roles/permisos + un Administrador inicial para el desarrollador.

---

## Decisiones P2

#### D-18 — Baja lógica de áreas y cascada dirección→área

- **Contexto:** Q-018 define baja lógica para direcciones; no dice nada de áreas. Q-004 hace que toda área dependa de una dirección.
- **Problema:** ¿Se pueden desactivar áreas? ¿Qué pasa con usuarios y tickets? ¿Se puede desactivar una dirección con áreas activas?
- **Decisión:** Áreas y Direcciones con `IsActive`; no se desactiva una dirección con áreas activas; no se desactiva un área con usuarios/tickets activos sin reasignación.

#### D-19 — Cambio de código de dirección

- **Contexto:** Q-018 ya define que el código es editable y único.
- **Problema:** ¿Se puede reutilizar un código antiguo? ¿Se audita el cambio?
- **Decisión:** Permitir cambio con auditoría; se puede utilizar cualquier código que no esté en uso de forma activa.

#### D-21 — Multi-área o multi-rol

- **Contexto:** El prompt indica “cada usuario pertenece a un área”; no define múltiples áreas o roles.
- **Problema:** ¿Un usuario puede pertenecer a varias áreas o tener varios roles?
- **Decisión:** Un usuario puede pertenecer a una o más áreas a través de una entidad intermedia. Un usuario puede tener un solo rol para cada área en la misma entidad intermedia.

#### D-23 — Política de contraseñas, bloqueo y MFA

- **Contexto:** No hay política definida; el legado sólo usa hash.
- **Problema:** ¿Longitud/complejidad? ¿Bloqueo por intentos? ¿Rate limiting? ¿MFA?
- **Decisión:** Política configurable, bloqueo temporal y rate limiting; MFA fuera de alcance inicial.

#### D-24 — Exportación

- **Contexto:** El legado exportaba desde el cliente; Q-020 amplía estadísticas.
- **Problema:** ¿Formatos? ¿Síncrona o asíncrona? ¿Límites?
- **Decisión:** CSV/XLSX para listados; PDF para reportes; exportación síncrona con límite y aviso.

#### D-25 — Ciclo de vida de comprobantes

- **Contexto:** Q-019 define un archivo por ticket y almacenamiento local; no define reemplazo, retención ni purga.
- **Problema:** ¿Se puede reemplazar? ¿Se purga con el ticket? ¿Se escanea?
- **Decisión:** No reemplazar tras completar; conservar con el ticket; escaneo opcional; purga física sólo con procedimiento auditado y explícito.

#### D-26 — Número visible de ticket

- **Contexto:** El legado tiene `nroArreglo`; Q-021 reinicia datos.
- **Problema:** ¿Secuencial global, por área/año o UUID visible?
- **Decisión:** Secuencial global simple, inmutable.

#### D-27 — Concurrencia e idempotencia

- **Contexto:** Dos usuarios pueden iniciar/completar el mismo ticket a la vez.
- **Problema:** ¿Cómo se detectan conflictos? ¿Se permiten reintentos idempotentes?
- **Decisión:** Versión/ETag con `If-Match`; conflicto `409`; `Idempotency-Key` opcional para altas.

---
---

## Resumen segunda ronda de decisiones

| ID | Tema | Prioridad |
|---|---|---|
| D-29 | Dirección del ticket | P0 |
| D-30 | Reapertura | P0 |
| D-31 | Permisos de inicio, complete, restore y reopen | P0 |
| D-32 | Reglas específicas de `INCIDENT` vs `TASK` | P1 |
| D-33 | Gestión de usuarios y autogestión | P1 |
| D-34 | Campos, estado inicial y motivos | P1 |
| D-35 | Listados, filtros y vistas por estado | P1 |
| D-36 | Último Administrador activo | P1 |
| D-37 | Gestión de configuración | P1 
| D-38 | Identificador de login y último acceso | P2 |
| D-39 | Auditoría de negocio vs logs de seguridad | P1 |

---

### 2.1 Prioridad P0 — bloquean la Fase 2

#### D-29 — Dirección del ticket

- **Contexto:** D-04 establece que una `Direction` agrupa áreas y que cada `Area` tiene `direction_id`. No responde si el ticket conserva una dirección afectada distinta de la dirección del área.
- **Decisión:** La dirección se deriva siempre de `Area.direction_id`; no hay `affected_direction_id`.

#### D-30 — Reapertura

- **Contexto:** D-13 aprueba reabrir tickets cerrados si reciben quejas, pero no define actor, estado destino, motivo, datos de cierre ni asignación.
- **Decisión:**
  - Permitir elegir destino (`PENDING`/`IN_PROGRESS`) y decidir por ticket.
  - Actores: Administrador para todo; Administrativo y Responsable de área por área; Agente no puede reabrir.
  - Motivo obligatorio.
  - El `assigned_user_id` debe ser el mismo que en el momento del cierre.
  - Como criterio para cierre, cancelación y eliminación, vamos a tener entidades aparte para persistir datos como de `solved_at`, `solution`, `receipt`, `cancelled_at`, `cancellation_reason`. Eso permite que en un primer cierre, el ticket pase a estado *cerrado* y la información del cierre pase a la tabla de cierres; si se reabre, vuelve al estado que se decida al reabrirlo y si hay un nuevo cierre se puede persistir también la nueva información.
  - Un ticket eliminado primero debe restaurarse.

#### D-31 — Permisos de inicio, complete, restore y reopen

- **Contexto:** D-05 enumera acciones de `ADMINISTRATIVE` (crear, editar propias, asignar) y `AREA_MANAGER` (crear, editar, asignar, reasignar, cancelar, eliminar). No dice si pueden iniciar o completar. D-12 sólo habla del `AGENT`. D-13 permite reabrir, pero no define actor. D-01 permite restaurar, pero no define actor.
- **Decisión:**
  - Administrativo: Inicia/reabre en su área; no completa/restaura.
  - Responsable de área: Inicia/completa/restaura/reabre en su área.

---

### 2.2 Prioridad P1

#### D-32 — Reglas específicas de `INCIDENT` vs `TASK`

- **Contexto:** La única diferencia aprobada es que `TASK` admite `problem_date` futura y `INCIDENT` no. No se definen prioridad, SLA, flujo, asignación, estadísticas ni campos.
- **Decisión:** Se diferencian por fecha futura y descripción funcional; se pueden contabilizar estadísticas por separado.

#### D-33 — Gestión de usuarios y autogestión

- **Contexto:** D-05 da alcance global al Admin, pero no confirma que sólo él gestione usuarios. Q-024 confirma cambio de rol por Admin. No está definido:
- si `ADMINISTRATIVE` puede ver/crear usuarios;
- si el usuario puede cambiar su propio rol;
- qué campos del perfil puede editar;
- si se excluyen inactivos de listados;
- si se exige contraseña actual para cambiarla.
- **Decisión:** Sólo Admin gestiona usuarios; el usuario edita nombre/apellido/correo y contraseña; no cambia rol/área; inactivos excluidos de listados operativos.

#### D-34 — Campos, estado inicial y motivos

- **Contexto:** No están aprobados:
  - obligatoriedad de `description`, `area_id`, `problem_date`, afectado y celular;
  - estado inicial (`PENDING` es propuesta);
  - obligatoriedad de `solution`;
  - motivo obligatorio de cancelación/eliminación;
  - campos editables y su inmutabilidad.
- **Decisión:** `type`, `description`, `area_id` y `problem_date` obligatorios; `PENDING` inicial; `solution` obligatoria; motivos obligatorios; edición limitada a abiertos.

#### D-35 — Listados, filtros y vistas por estado

- **Contexto:** El legado tenía listados “actual”, “completadas”, “completadas hoy”, “canceladas” y “eliminadas”. No están aprobados como reglas. Tampoco los filtros exactos.
- **Decisión:** Actual = `PENDING` + `IN_PROGRESS`; completados, cancelados y eliminados separados; filtros por tipo, área, responsable, creador y fechas. Con filtros server-side.

#### D-36 — Último Administrador activo

- **Contexto:** D-05 y Q-024 permiten al Admin gestionar roles, pero no definen protección del último Admin ni auto-baja.
- **Decisión:** Bloquear baja, auto-baja y cambio de rol del último Admin activo.

#### D-37 — Gestión de configuración

- **Contexto:** D-14 permite al Administrador cambiar la zona horaria, pero no define UI/API ni otros parámetros.
- **Decisión:** `/settings` sólo con `timezone` para primera versión; configuración ampliada a futuro.

#### D-39 — Auditoría de negocio vs logs de seguridad

- **Contexto:** D-22 dice que se auditan sólo cambios de negocio. Login, logout, intentos fallidos y bloqueos no son cambios de negocio, pero son relevantes para seguridad.
- **Decisión:** Auditoría de negocio separada de logs de seguridad; no se exponen por `/audit-events`.

### 2.3 Prioridad P2

#### D-38 — Identificador de login y último acceso

- **Contexto:** El legado permitía usuario o correo. D-16 hace obligatorio el correo, pero no confirma el identificador de login ni el registro de último acceso.
- **Decisión:** Login por usuario o correo; registrar `last_login_at`. Preferentemente, que el usuario sea igual al correo (ejemplo, si el correo es *apellido.nombre@organizacion.com*, que el usuario sea *apellido.nombre*).
