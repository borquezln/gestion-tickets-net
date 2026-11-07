# Fase 1 — Especificación funcional y diseño del dominio

> **Revisión 4 — actualizada con las decisiones D-01 a D-39 y las aclaraciones de la Revisión 4.**
> Estado: propuesta para revisión. No se inició la Fase 2, no se escribió código, no se crearon proyectos ASP.NET Core ni React, no se instalaron dependencias y no se modificó el esquema de base de datos ni ningún archivo de `/legacy`.

## 1. Propósito

Transformar el análisis de la Fase 0, `docs/definitions.md` y las decisiones aprobadas de `docs/specification-definitions.md` en una especificación funcional y un diseño de dominio coherentes para una futura API ASP.NET Core y un frontend React.

Esta revisión incorpora:

1. **Tickets de tipo Incidente o Tarea.** Un ticket puede ser un `INCIDENT` (problema específico reportado por un área) o un `TASK` (problema preexistente o mejora a realizar en un área).
2. **Modelo en inglés.** Entidades, atributos, estados, roles, permisos y eventos usan nombres en inglés. Por ejemplo, `fechaProblema` pasa a `problem_date`; la entidad `Tarea` pasa a `Ticket`.
3. **Ninguna regla de negocio del legado es válida sin aprobación explícita.** Las reglas heredadas se clasifican como aprobadas, pendientes, reemplazadas o descartadas; no se asumen por provenir del PHP original.
4. **Decisiones D-29 a D-39 incorporadas.** Dirección derivada del área, reapertura y cierres históricos, permisos operativos, gestión de usuarios, campos obligatorios, listados, último superusuario, configuración, login y separación de auditoría.

## 2. Fuentes y prioridad

| Prioridad | Fuente | Uso |
|---|---|---|
| 1 | `docs/specification-definitions.md` | Decisiones aprobadas de Fase 1; prevalece ante diferencias. |
| 2 | `docs/definitions.md` | Definiciones funcionales y de negocio originales. |
| 3 | `docs/analysis/*` | Evidencia del sistema legado. Sólo informa; no aprueba reglas. |

> **Nota de árbol de trabajo:** las definiciones originales se citan como `docs/definitions.md` por su nombre funcional. En el árbol actual ese contenido está archivado como `docs/analysis-definitions.md`; el cambio de nombre es preexistente y no fue realizado por esta revisión.

Si una contradicción no puede resolverse con las dos primeras fuentes, se documenta y se solicita intervención del usuario. No se completa por suposición.

## 3. Documentos generados

| Documento | Contenido |
|---|---|
| [`functional-specification.md`](./functional-specification.md) | Especificación consolidada, decisiones aprobadas, reglas del legado y trazabilidad. |
| [`roles-and-permissions.md`](./roles-and-permissions.md) | Cuatro roles aprobados, permisos, matriz y alcance por área/asignación. |
| [`task-lifecycle.md`](./task-lifecycle.md) | Ciclo de vida de tickets, tipos, transiciones, reapertura, cierres, eliminación y restauración. |
| [`domain-model.md`](./domain-model.md) | Modelo en inglés: `Ticket`, `TicketClosure`, `TicketType`, `TicketStatus`, `UserAreaMembership`, etc. |
| [`use-cases.md`](./use-cases.md) | Casos de uso por actor, actualizados a tickets y a las decisiones aprobadas. |
| [`api-contracts.md`](./api-contracts.md) | Recursos y operaciones HTTP sobre `/tickets`, usuarios, catálogos, estadísticas y auditoría. |
| [`security-and-sessions.md`](./security-and-sessions.md) | Cookie de sesión, expiración deslizante, invalidación, email obligatorio, login y zona horaria. |
| [`reporting-and-files.md`](./reporting-and-files.md) | KPIs, exportación, comprobantes PDF por cierre y acceso autenticado. |
| [`open-decisions.md`](./open-decisions.md) | Registro de decisiones D-01 a D-39, contradicciones resueltas y riesgos residuales. |

## 4. Convenciones

### 4.1 Identificadores

| Prefijo | Significado |
|---|---|
| `Q-xxx` | Pregunta o definición de `docs/definitions.md`. |
| `D-xxx` | Decisión de `docs/specification-definitions.md` (D-01 a D-39). |
| `BR-xxx` | Regla del análisis legado. |
| `RN-xxx` | Regla aprobada en esta especificación. |
| `PR-xxx` | Regla aprobada en la segunda ronda; se conserva para trazabilidad. |
| `UC-xxx` | Caso de uso. |
| `P-xxx` | Permiso. |
| `S-xxx` | Alcance de datos. |
| `KPI-xxx` | Indicador de estadísticas. |

### 4.2 Estados de una regla

| Marca | Significado |
|---|---|
| **[A]** | Aprobada explícitamente por `definitions.md` o `specification-definitions.md`. |
| **[P]** | No aprobada; se mantiene fuera del alcance hasta definición. |
| **[R]** | Reemplazada por una decisión aprobada. |
| **[D]** | Descartada por contradecir una decisión aprobada. |
| **[S]** | Control técnico o de seguridad, no regla de negocio. |

### 4.3 Terminología

- **Ticket:** entidad central. Reemplaza a “tarea” cuando se habla del requerimiento.
- **`INCIDENT`:** ticket que representa un problema específico reportado por un área.
- **`TASK`:** ticket que representa un problema preexistente o una mejora a realizar en un área.
- **`PENDING`, `IN_PROGRESS`, `COMPLETED`, `CANCELLED`:** estados del ciclo de vida.
- **`TicketClosure`:** entidad que conserva los datos de cada cierre (`solved_at`, `solution`, `receipt`, `cancelled_at`, `cancellation_reason`). Un ticket puede tener varios cierres históricos.
- **`UserAreaMembership`:** entidad intermedia que vincula un usuario con un área y un rol. Un usuario puede tener varias membresías; una por área, con un solo rol por área.
- **`problem_date`:** fecha declarada del problema, editable en tickets abiertos.
- **`created_at`:** timestamp del registro, generado por el servidor.
- **`SUPERUSER`:** código inglés del rol que D-02 denomina “Administrador”; se usa para no confundirlo con `ADMINISTRATIVE`.

## 5. Decisiones aprobadas D-01 a D-28

| ID | Decisión aprobada |
|---|---|
| D-01 | Cuatro estados (`PENDING`, `IN_PROGRESS`, `COMPLETED`, `CANCELLED`) + marca de eliminación. Restaurar limpia la marca y conserva el estado. |
| D-02 | Roles: `SUPERUSER`, `ADMINISTRATIVE`, `AREA_MANAGER`, `AGENT`. |
| D-03 | `SUPERUSER` global; `ADMINISTRATIVE` y `AREA_MANAGER` por área; `AGENT` sólo tickets asignados y sin asignar de su área. |
| D-04 | Una `Direction` agrupa áreas; cada `Area` tiene `direction_id`; “área operativa” es la misma entidad que “área dependiente de una dirección”. |
| D-05 | `SUPERUSER` global; `ADMINISTRATIVE` crea, edita los creados por él y asigna; `AREA_MANAGER` crea, edita, asigna, reasigna, cancela y elimina; `AGENT` toma tickets sin asignar. El responsable debe estar activo, pertenecer al área y tener rol `AGENT` o `AREA_MANAGER`. Iniciar no exige responsable. |
| D-06 | Bloquear baja/cambio de área de un responsable con tickets activos hasta reasignar o desasignar, con aviso explícito. |
| D-07 | Cookie de sesión `HttpOnly` + sesión de servidor; 60 minutos deslizantes; invalidar al dar de baja, cambiar rol/área o blanquear contraseña; exigir nuevo blanqueo al reactivar. |
| D-08 | Reportes históricos por evento con filtro explícito; el panel operativo excluye los eliminados y los muestra aparte; documentar el área actual. |
| D-09 | Nota Electrónica: identificador externo opcional, formato/longitud configurables, visible para roles operativos, sin checkbox separado. |
| D-10 | `problem_date` es fecha y hora, editable en `PENDING`/`IN_PROGRESS`, con valor inicial configurable; los tickets de tipo `TASK` pueden tener fecha futura; los `INCIDENT` no. |
| D-11 | PDF obligatorio para completar; tamaño máximo configurable; validación en servidor. |
| D-12 | `AGENT` puede tomar tickets sin asignar y transicionar los propios: iniciar, completar, cancelar. |
| D-13 | Los tickets cerrados son inmutables en ese estado. Se pueden reabrir si reciben quejas, o eliminar y restaurar posteriormente. |
| D-14 | Zona horaria `America/Argentina/Buenos_Aires` por defecto, configurable por el Administrador. |
| D-15 | El legajo es inmutable por regla de negocio; corrección excepcional mediante procedimiento administrativo auditado. |
| D-16 | El correo es obligatorio y debe ser interno de la organización. |
| D-17 | Catálogo fijo de roles con permisos declarativos; `P-ROLE_MANAGE` (denominación en inglés de `P-ROL-GESTIONAR`) queda reservado. |
| D-18 | Áreas y direcciones con `is_active`; no se desactiva una dirección con áreas activas; no se desactiva un área con usuarios/tickets activos sin reasignación. |
| D-19 | El código de dirección puede cambiarse con auditoría; se puede reutilizar cualquier código que no esté en uso activo. |
| D-20 | Los tickets referencian el `id` del usuario y muestran/buscan por legajo. |
| D-21 | Un usuario puede pertenecer a una o más áreas mediante una entidad intermedia; un solo rol por área en esa entidad. |
| D-22 | Se auditan sólo cambios de negocio; en principio se retiene todo. |
| D-23 | Política de contraseñas configurable, bloqueo temporal y rate limiting; MFA fuera de alcance inicial. |
| D-24 | CSV/XLSX para listados; PDF para reportes; exportación síncrona con límite y aviso. |
| D-25 | No reemplazar el comprobante tras completar; conservarlo con el cierre; escaneo opcional; purga física sólo con procedimiento auditado y explícito. |
| D-26 | Número visible de ticket secuencial global simple, inmutable. |
| D-27 | Versión/ETag con `If-Match`; conflicto `409`; `Idempotency-Key` opcional para altas. |
| D-28 | Seed de roles/permisos + un `SUPERUSER` inicial para el desarrollador. |

## 6. Decisiones D-29 a D-39 incorporadas

| ID | Decisión aprobada |
|---|---|
| D-29 | La dirección del ticket se deriva siempre de `Area.direction_id`; no existe `affected_direction_id`. |
| D-30 | La reapertura vuelve a `PENDING` o `IN_PROGRESS`; exige motivo y el mismo responsable del cierre; los datos de cierre se persisten en `TicketClosure`; un ticket eliminado se restaura primero. |
| D-31 | `ADMINISTRATIVE` inicia/reabre en su área y no completa/restaura; `AREA_MANAGER` inicia/completa/restaura/reabre en su área; `AGENT` inicia/completa/cancela los propios; `SUPERUSER` todo. |
| D-32 | `INCIDENT` y `TASK` se diferencian por fecha futura y descripción funcional; pueden contabilizarse por separado. |
| D-33 | Sólo el `SUPERUSER` gestiona usuarios; el usuario edita nombre, apellido, correo y contraseña, no rol/área; los inactivos se excluyen de listados operativos. |
| D-34 | `type`, `description`, `area_id` y `problem_date` son obligatorios; estado inicial `PENDING`; `solution` y motivos de cancelación/eliminación obligatorios; sólo se editan tickets abiertos. |
| D-35 | Listado actual = `PENDING` + `IN_PROGRESS`; completados, cancelados y eliminados separados; filtros server-side por tipo, área, responsable, creador y fechas. |
| D-36 | Se bloquea la baja, auto-baja y cambio de rol del último `SUPERUSER` activo. |
| D-37 | `/settings` sólo expone `timezone` en la primera versión. |
| D-38 | Login por usuario o correo; se registra `last_login_at`. |
| D-39 | La auditoría de negocio se separa de los logs de seguridad; los eventos de seguridad no se exponen por `/audit-events`. |

## 7. Criterios de aceptación de la revisión 4

1. Todas las decisiones D-01 a D-39 están reflejadas sin contradicciones.
2. La entidad central es `Ticket`; existen `INCIDENT` y `TASK`.
3. El modelo, campos, estados, roles, permisos y eventos están en inglés.
4. Ninguna regla del legado se presenta como definitiva sin aprobación explícita.
5. Los roles aprobados son exactamente cuatro; el código `SUPERUSER` reemplaza a `ADMIN` y no se reintroduce “Consulta”.
6. La visibilidad del `AGENT` incluye asignados y sin asignar de su área.
7. La reapertura, el cierre y los permisos de D-30/D-31 están modelados.
8. La pertenencia multi-área con un rol por área está modelada.
9. Las decisiones D-29 a D-39 están incorporadas y no quedan P0/P1/P2 abiertas.
10. No se creó código, proyecto, dependencia ni migración.

## 8. Restricciones respetadas

- `/legacy` no fue modificado.
- No se modificó el contenido de `docs/definitions.md` (hoy `docs/analysis-definitions.md`) ni de `docs/specification-definitions.md`.
- No se escribió C# ni JavaScript.
- No se crearon proyectos ASP.NET Core ni React.
- No se instalaron dependencias.
- No se crearon migraciones ni se modificó ningún esquema de base de datos.
- No se inventaron reglas para cubrir vacíos; los riesgos residuales están en `open-decisions.md`.

## 9. Próximo paso

La documentación queda lista para revisión y aprobación. Una vez aprobada, se podrá iniciar la Fase 2 sin arrastrar decisiones P0/P1/P2 abiertas.
