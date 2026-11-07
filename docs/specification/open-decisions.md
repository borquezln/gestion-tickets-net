# Decisiones, contradicciones y riesgos residuales

> **Revisión 4.** Las decisiones D-01 a D-39 de `docs/specification-definitions.md` están aprobadas e incorporadas. Las aclaraciones de la Revisión 4 están incorporadas. No quedan decisiones P0/P1/P2 abiertas. Este documento conserva el registro de decisiones, las contradicciones resueltas y los riesgos residuales que no bloquean la especificación.

## 1. Registro de decisiones D-01 a D-39

### D-01 — Cuatro estados y eliminación lógica
- **Decisión:** Cuatro estados (`PENDING`, `IN_PROGRESS`, `COMPLETED`, `CANCELLED`) más marca de eliminación; restaurar conserva el estado previo.
- **Impacto:** `Ticket`, ciclo de vida, listados, reportes e historial.

### D-02 — Cuatro roles fijos
- **Decisión:** Roles exactos: `SUPERUSER`, `ADMINISTRATIVE`, `AREA_MANAGER`, `AGENT`. No existe “Consulta”.
- **Impacto:** Permisos, alcance, catálogo y seed.

### D-03 — Alcance por rol
- **Decisión:** `SUPERUSER` global; `ADMINISTRATIVE` y `AREA_MANAGER` por área; `AGENT` ve asignados y sin asignar de su área.
- **Impacto:** Visibilidad, autorización y listados.

### D-04 — Dirección y área
- **Decisión:** Una `Direction` agrupa áreas; cada `Area` tiene `direction_id`; “área operativa” es la misma entidad que “área dependiente de una dirección”.
- **Impacto:** Modelo organizacional y alcance.

### D-05 — Permisos operativos por rol
- **Decisión:** `ADMINISTRATIVE` crea, edita los creados por él y asigna; `AREA_MANAGER` crea, edita, asigna, reasigna, cancela y elimina; `AGENT` toma sin asignar; iniciar no exige responsable.
- **Impacto:** Permisos y transiciones.

### D-06 — Responsable con tickets activos
- **Decisión:** Se bloquea baja o cambio de área de un responsable con tickets activos hasta reasignar o desasignar, con aviso explícito.
- **Impacto:** Usuarios y asignación.

### D-07 — Sesión y expiración
- **Decisión:** Cookie `HttpOnly` + sesión de servidor; 60 minutos deslizantes; invalidación por baja, cambio de rol/área o blanqueo; nuevo blanqueo al reactivar.
- **Impacto:** Seguridad y sesiones.

### D-08 — Reportes históricos
- **Decisión:** Reportes históricos por evento con filtro explícito; panel operativo excluye eliminados y los muestra aparte; documentar el área actual.
- **Impacto:** Reporting y estadísticas.

### D-09 — Nota Electrónica
- **Decisión:** Nota Electrónica como identificador externo opcional, con formato/longitud configurables.
- **Impacto:** `Ticket` y validación.

### D-10 — Fecha del problema
- **Decisión:** `problem_date` es fecha y hora, editable en `PENDING`/`IN_PROGRESS`, con valor inicial configurable; `TASK` puede ser futura; `INCIDENT` no.
- **Impacto:** Validación y tipos de ticket.

### D-11 — Comprobante PDF
- **Decisión:** PDF obligatorio para completar; tamaño máximo configurable; validación en servidor.
- **Impacto:** Comprobantes y cierre.

### D-12 — Toma y transición por `AGENT`
- **Decisión:** `AGENT` puede tomar tickets sin asignar y transicionar los propios: iniciar, completar, cancelar.
- **Impacto:** Permisos y ciclo de vida.

### D-13 — Cerrados inmutables y reapertura
- **Decisión:** Los tickets cerrados son inmutables en ese estado; se pueden reabrir si reciben quejas, o eliminar y restaurar posteriormente.
- **Impacto:** Ciclo de vida; detalles en D-30.

### D-14 — Zona horaria
- **Decisión:** Zona horaria `America/Argentina/Buenos_Aires` por defecto, configurable por el Administrador.
- **Impacto:** Configuración y fechas.

### D-15 — Legajo inmutable
- **Decisión:** El legajo es inmutable; corrección excepcional mediante procedimiento administrativo auditado.
- **Impacto:** `User`.

### D-16 — Correo obligatorio e interno
- **Decisión:** El correo es obligatorio y debe ser interno de la organización.
- **Impacto:** `User`, login y validación.

### D-17 — Catálogo fijo de roles
- **Decisión:** Catálogo fijo de roles con permisos declarativos; `P-ROLE_MANAGE` queda reservado.
- **Impacto:** Roles y permisos.

### D-18 — Activación de áreas y direcciones
- **Decisión:** Áreas y direcciones con `is_active`; no se desactiva una dirección con áreas activas; no se desactiva un área con usuarios/tickets activos sin reasignación.
- **Impacto:** Catálogos y validación.

### D-19 — Código de dirección
- **Decisión:** El código de dirección puede cambiarse con auditoría; se puede reutilizar cualquier código que no esté en uso activo.
- **Impacto:** `Direction`.

### D-20 — Referencia a usuario
- **Decisión:** Los tickets referencian el `id` del usuario y muestran/buscan por legajo.
- **Impacto:** `Ticket` y `User`.

### D-21 — Multi-área
- **Decisión:** Un usuario puede pertenecer a una o más áreas mediante `UserAreaMembership`; un solo rol por área en esa entidad.
- **Impacto:** Identidad, alcance y permisos.

### D-22 — Auditoría de negocio
- **Decisión:** Se auditan sólo cambios de negocio; en principio se retiene todo.
- **Impacto:** Auditoría y retención.

### D-23 — Contraseñas y bloqueo
- **Decisión:** Política de contraseñas configurable, bloqueo temporal y rate limiting; MFA fuera de alcance inicial.
- **Impacto:** Seguridad y autenticación.

### D-24 — Exportación
- **Decisión:** CSV/XLSX para listados; PDF para reportes; exportación síncrona con límite y aviso.
- **Impacto:** Reporting.

### D-25 — Conservación del comprobante
- **Decisión:** No reemplazar el comprobante tras completar; conservarlo con el cierre; escaneo opcional; purga física sólo con procedimiento auditado y explícito.
- **Impacto:** Archivos; alcance por cierre en D-30.

### D-26 — Número visible de ticket
- **Decisión:** Número visible de ticket secuencial global simple, inmutable.
- **Impacto:** `Ticket`.

### D-27 — Concurrencia e idempotencia
- **Decisión:** Versión/ETag con `If-Match`; conflicto `409`; `Idempotency-Key` opcional para altas.
- **Impacto:** API y concurrencia.

### D-28 — Seed inicial
- **Decisión:** Seed de roles/permisos + un `SUPERUSER` inicial para el desarrollador. Las aclaraciones de la Revisión 4 exigen que se cree sin contraseña utilizable.
- **Impacto:** Inicialización y seguridad.

### D-29 — Dirección del ticket
- **Decisión:** La dirección del ticket se deriva siempre de `Area.direction_id`; no existe `affected_direction_id`.
- **Impacto:** `Ticket`, formularios, filtros, reportes, API y consultas por dirección. Se elimina `affected_direction_id` del modelo y de los contratos.

### D-30 — Reapertura y cierres
- **Decisión:** La reapertura se permite desde `COMPLETED` o `CANCELLED`; el estado destino se elige por ticket (`PENDING` o `IN_PROGRESS`); exige motivo; pueden reabrir `SUPERUSER`, `ADMINISTRATIVE` y `AREA_MANAGER` según alcance; `AGENT` no; el responsable debe ser el mismo que tenía al cierre; un ticket eliminado debe restaurarse antes de reabrir; los datos de cierre se persisten en `TicketClosure`; un ticket puede tener varios cierres históricos; el comprobante pertenece al cierre.
- **Impacto:** `Ticket`, `TicketClosure`, `TicketReceipt`, API de cierre/reabierto, historial, reportes y comprobantes.

### D-31 — Permisos operativos
- **Decisión:** `ADMINISTRATIVE` inicia y reabre en su área y no completa/restaura; `AREA_MANAGER` inicia, completa, restaura y reabre en su área; `AGENT` inicia, completa y cancela los propios; `SUPERUSER` puede todo.
- **Impacto:** Autorización de `/start`, `/completion`, `/cancellation`, `/restoration` y `/reopen`.

### D-32 — Tipos de ticket
- **Decisión:** `INCIDENT` y `TASK` se diferencian por fecha futura y descripción funcional; `INCIDENT` no admite `problem_date` futura; `TASK` sí; pueden contabilizarse por separado.
- **Impacto:** Validación, filtros y estadísticas.

### D-33 — Gestión de usuarios y autogestión
- **Decisión:** Sólo el `SUPERUSER` gestiona usuarios, membresías y roles; el usuario edita nombre, apellido, correo y contraseña; no cambia rol ni áreas; los inactivos se excluyen de listados operativos.
- **Impacto:** `/me`, `/users`, membresías y campos editables.

### D-34 — Campos, estado inicial y motivos
- **Decisión:** `type`, `description`, `area_id` y `problem_date` son obligatorios; estado inicial `PENDING`; `solution` obligatoria para completar; motivos obligatorios para cancelar y eliminar; sólo se editan tickets `PENDING` o `IN_PROGRESS`; la edición no altera campos inmutables.
- **Impacto:** `POST/PATCH /tickets`, `/completion`, `/cancellation`, `/deletion`.

### D-35 — Listados y filtros
- **Decisión:** Listado actual = `PENDING` + `IN_PROGRESS`; completados, cancelados y eliminados tienen listados separados; filtros server-side por tipo, área, responsable, creador y fechas.
- **Impacto:** `GET /tickets` y vistas por estado.

### D-36 — Último superusuario activo
- **Decisión:** Se bloquea la baja, auto-baja y cambio de rol del último `SUPERUSER` activo.
- **Impacto:** `/deactivation`, cambio de rol y baja.

### D-37 — Configuración
- **Decisión:** `/settings` sólo expone `timezone` en la primera versión; la configuración ampliada queda para versiones futuras.
- **Impacto:** `/settings`.

### D-38 — Login y último acceso
- **Decisión:** El login acepta usuario o correo; preferentemente el usuario coincide con la parte local del correo; se registra `last_login_at`; los usuarios inactivos no inician sesión.
- **Impacto:** `/auth/login`, `/me`, `last_login_at`.

### D-39 — Auditoría de negocio y logs de seguridad
- **Decisión:** La auditoría de negocio se separa de los logs de seguridad; login, logout, intentos fallidos, bloqueos y rate limiting no se exponen por `/audit-events`.
- **Impacto:** `/audit-events` y `SecurityEvent`.

## 2. Aclaraciones cerradas en la Revisión 4

| Tema | Tratamiento |
|---|---|
| Diferencias `INCIDENT` vs `TASK` | Se conservan las diferencias aprobadas por D-32: fecha futura y descripción funcional; pueden contabilizarse por separado. No se agregan reglas no aprobadas. |
| Responsable histórico de cierres | No se agrega `assigned_user_id` a `TicketClosure`; se reconstruye desde `TicketTimelineEntry`. |
| Reapertura/restauración con asignación inválida | Exige reasignación o desasignación antes de completar la operación. |
| Trazabilidad de eliminaciones/restauraciones | Se registran como eventos históricos en `TicketTimelineEntry`; los campos actuales de `Ticket` sólo reflejan la última operación. |
| Historial vs auditoría | `P-TICKET_VIEW_HISTORY` y `P-AUDIT_VIEW` permanecen separados; `AGENT` accede a auditoría sólo de sus tickets asignados. |
| Seed del `SUPERUSER` inicial | Se crea sin contraseña utilizable; debe establecer contraseña antes de operar. |
| Activación inicial del `SUPERUSER` | Requiere token de un solo uso, vencimiento y entrega segura en Fase 2. |

## 3. Contradicciones resueltas

| ID | Contradicción / ambigüedad | Tratamiento |
|---|---|---|
| C-01 | “Área operativa” vs “área dependiente de dirección”. | D-04: son la misma entidad. |
| C-02 | Eliminación como estado vs restauración. | D-01: cuatro estados + marca de eliminación. |
| C-03 | Q-010 vs Q-020. | D-08: panel operativo vs reporte histórico por evento. |
| C-04 | “Sesiones y/o contraseña” en Q-001. | D-07: invalidar sesiones y exigir blanqueo al reactivar. |
| C-05 | Correo opcional vs login por correo. | D-16: correo obligatorio e interno; D-38: login por usuario o correo. |
| C-06 | Roles nuevos vs catálogo legado. | D-02: cuatro roles fijos. |
| C-07 | Un usuario, un área/rol vs multi-área. | D-21: multi-área con un rol por área. |
| C-08 | Nota Electrónica externa vs texto libre. | D-09: identificador externo opcional. |
| C-09 | Reapertura prohibida vs quejas. | D-13/D-30: reapertura con destino `PENDING`/`IN_PROGRESS`, motivo y mismo responsable. |
| C-10 | Jerarquía de direcciones vs áreas. | D-04: dirección agrupa áreas; sin jerarquía adicional. |
| C-11 | Ticket elimina estado vs conserva estado. | D-01: eliminación separada del estado. |
| C-12 | Sólo `SUPERUSER` gestiona usuarios vs D-05 global. | D-33: sólo el `SUPERUSER` gestiona usuarios. |
| C-13 | Correo visible vs correo interno. | D-16: obligatorio e interno; visibilidad según permisos. |
| C-14 | Asignación obligatoria vs inicio sin responsable. | D-05: responsable opcional; iniciar no exige responsable. |
| C-15 | Edición de cerrados vs inmutabilidad. | D-13/D-34: sólo se editan abiertos. |
| C-16 | Eliminación física vs historial. | D-01/D-18/D-25: eliminación lógica; purga física sólo auditada. |
| C-17 | Dirección del ticket: área vs afectado. | D-29: se deriva de `Area.direction_id`. |
| C-18 | Reapertura: actor, destino, motivo y datos de cierre. | D-30: destino `PENDING`/`IN_PROGRESS`, motivo, mismo responsable y `TicketClosure`. |
| C-19 | Permisos de inicio/complete/restore/reopen. | D-31. |
| C-20 | Diferencias entre `INCIDENT` y `TASK`. | D-32: fecha futura, descripción funcional y estadísticas separadas. |
| C-21 | Gestión de usuarios, autogestión y campos editables. | D-33. |
| C-22 | Campos obligatorios, estado inicial, solución y motivos. | D-34. |
| C-23 | Listados y filtros por estado. | D-35. |
| C-24 | Protección del último Administrador. | D-36. |
| C-25 | Configuración de zona horaria y parámetros. | D-37. |
| C-26 | Identificador de login y último acceso. | D-38. |
| C-27 | Auditoría de negocio vs logs de seguridad. | D-39. |
| C-28 | “Ninguna regla del legado es válida sin aprobación” vs reglas operativas necesarias. | Se clasifican en aprobadas, pendientes o controles técnicos. |

## 4. Riesgos residuales

No son decisiones abiertas; no bloquean la Fase 2.

| Riesgo | Impacto | Tratamiento |
|---|---|---|
| Sin migración de históricos (Q-021) | Medio: pérdida de continuidad operativa. | Comunicar alcance; el sistema se inicializa de cero. |
| `TicketClosure` agrega una entidad y cambia el modelo de comprobantes | Medio: mayor complejidad de consultas. | Implementar relación ticket–cierres y conservar comprobantes históricos. |
| Obligatoriedad de campos de usuario no detallada (BR-014) | Bajo/medio: validaciones de alta. | Definir en Fase 2; correo y legajo ya son obligatorios. |
| Requisito de contraseña actual para cambio propio | Bajo: control de seguridad. | Tratarlo como control técnico en Fase 2. |
| Retención y acceso a logs de seguridad (D-39) | Bajo/medio: operación y auditoría. | Definir política operativa de logs. |
| Edición de `username` no definida | Bajo: gestión de usuarios. | Mantener `username` estable y resolver en Fase 2. |
| Visibilidad de eliminados para `AGENT` | Bajo: listados. | D-03/D-35: el `AGENT` no recibe listado de eliminados en la primera versión. |
| Reconstrucción del responsable histórico | Medio: depende de la completitud del historial. | Exige eventos de asignación con responsable anterior/posterior y creación con asignación inicial. |
| Interacción de reasignación en reopen/restore | Bajo/medio: UX y validación. | Definir en Fase 2 el flujo exacto de reasignación o desasignación. |
| Activación inicial del `SUPERUSER` | Medio: seguridad. | Implementar token de un solo uso, vencimiento y entrega segura en Fase 2. |

## 5. Trazabilidad

| Documento | Decisiones incorporadas |
|---|---|
| `README.md` | D-01 a D-39. |
| `functional-specification.md` | D-01 a D-39. |
| `roles-and-permissions.md` | D-02, D-03, D-05, D-12, D-17, D-21, D-29, D-30, D-31, D-33, D-35, D-36, D-39. |
| `task-lifecycle.md` | D-01, D-05, D-10, D-11, D-12, D-13, D-29, D-30, D-31, D-32, D-34, D-35. |
| `domain-model.md` | D-01 a D-05, D-09 a D-11, D-15 a D-22, D-25 a D-30, D-32 a D-39. |
| `use-cases.md` | D-01 a D-39. |
| `api-contracts.md` | D-01 a D-39. |
| `security-and-sessions.md` | D-07, D-14, D-16, D-22, D-23, D-33, D-36, D-37, D-38, D-39. |
| `reporting-and-files.md` | D-08, D-11, D-24, D-25, D-30, D-32, D-34, D-35, D-37, D-39. |

## 6. Próximo paso

La especificación queda lista para revisión y aprobación. No hay decisiones abiertas que impidan iniciar la Fase 2 cuando el usuario lo autorice.
