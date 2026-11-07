# Roles, permisos y alcance

> **Fase 1 — Revisión 4.** Basado en `docs/specification-definitions.md` (D-01 a D-39) y las aclaraciones de la Revisión 4.
> Este documento distingue permisos de acción de alcance de datos. Un permiso no implica acceso global.

## 1. Decisiones que gobiernan este documento

| Decisión | Efecto |
|---|---|
| D-02 | Catálogo exacto de cuatro roles: `SUPERUSER`, `ADMINISTRATIVE`, `AREA_MANAGER`, `AGENT`. |
| D-03 | `SUPERUSER` global; `ADMINISTRATIVE` y `AREA_MANAGER` por área; `AGENT` ve asignados y sin asignar de su área. |
| D-05 | `ADMINISTRATIVE` crea, edita creados propios y asigna; `AREA_MANAGER` crea, edita, asigna, reasigna, cancela y elimina; `AGENT` toma sin asignar; iniciar no exige responsable. |
| D-07 | Cambios de rol/área invalidan sesiones. |
| D-12 | `AGENT` puede tomar tickets sin asignar y transicionar los propios: iniciar, completar, cancelar. |
| D-17 | Catálogo fijo de roles; `P-ROLE_MANAGE` reservado. |
| D-21 | Multi-área mediante `UserAreaMembership`; un solo rol por área. |
| D-29 | La dirección del ticket se deriva de `Area.direction_id`; no hay dirección afectada separada. |
| D-30 | Reapertura con destino `PENDING`/`IN_PROGRESS`, motivo obligatorio, mismo responsable y `TicketClosure`. |
| D-31 | `ADMINISTRATIVE` inicia/reabre en su área y no completa/restaura; `AREA_MANAGER` inicia/completa/restaura/reabre en su área. |
| D-33 | Sólo el `SUPERUSER` gestiona usuarios; autogestión limitada; inactivos excluidos de listados operativos. |
| D-35 | Listados y filtros por estado; eliminados en listado separado. |
| D-36 | Protección del último `SUPERUSER` activo. |
| D-39 | Auditoría de negocio separada de logs de seguridad. |

## 2. Catálogo de roles

| Código | Nombre | Ámbito | Descripción |
|---|---|---|---|
| `SUPERUSER` | Administrador | Global | Administra el sistema completo, usuarios, membresías, catálogos, configuración, tickets y auditoría. |
| `ADMINISTRATIVE` | Administrativo | Por área | Crea tickets, edita los creados por él, asigna, inicia y reabre tickets de su área. No completa ni restaura. |
| `AREA_MANAGER` | Responsable de área | Por área | Crea, edita, asigna, reasigna, inicia, completa, cancela, elimina, restaura y reabre tickets de su área. |
| `AGENT` | Agente | Por área | Toma tickets sin asignar y ejecuta los propios: inicia, completa y cancela. |

No existe `CONSULTANT` ni “Consulta”. El catálogo es fijo; no se crean roles en runtime.

> **Nomenclatura:** D-02 denomina “Administrador” a este rol. En inglés se codifica `SUPERUSER` para no confundirlo con `ADMINISTRATIVE`.

## 3. Membresía multi-área

### 3.1 Entidad

`UserAreaMembership`:

| Campo | Tipo | Regla |
|---|---|---|
| `id` | uuid | PK. |
| `user_id` | uuid | FK a `User`. |
| `area_id` | uuid | FK a `Area`. |
| `role_id` | uuid | FK a `Role`. |
| `is_active` | boolean | Membresía vigente. |
| `created_at` | timestamp | Generado por servidor. |
| `updated_at` | timestamp | Generado por servidor. |

### 3.2 Invariantes

1. Un usuario puede tener una o más membresías.
2. No puede tener dos membresías activas para la misma área.
3. Cada membresía tiene exactamente un rol.
4. Un usuario puede tener roles distintos en áreas distintas.
5. Si una membresía es `SUPERUSER`, el usuario tiene alcance global.
6. Si un usuario tiene `ADMINISTRATIVE` en un área y `AGENT` en otra, opera como Administrativo en la primera y como Agente en la segunda.
7. El cambio de rol o de área de una membresía invalida las sesiones del usuario (D-07).
8. La baja de una membresía no borra tickets ni historial.
9. No se puede quitar el rol al último `SUPERUSER` activo (D-36).

### 3.3 Ejemplos

| Usuario | Membresías | Efecto |
|---|---|---|
| A | `SUPERUSER` en Área 1 | Alcance global. |
| B | `AREA_MANAGER` en Área 1; `AGENT` en Área 2 | Responsable en Área 1; Agente en Área 2. |
| C | `ADMINISTRATIVE` en Área 1; `ADMINISTRATIVE` en Área 2 | Administrativo en ambas áreas. |
| D | `AGENT` en Área 1; `AGENT` en Área 2 | Ve asignados y sin asignar de ambas áreas. |

## 4. Permisos

### 4.1 Catálogo

| Código | Recurso | Acción | Descripción |
|---|---|---|---|
| `P-USER_VIEW` | User | Ver | Ver usuarios. |
| `P-USER_CREATE` | User | Crear | Crear usuarios. |
| `P-USER_EDIT` | User | Editar | Editar datos de usuario. |
| `P-USER_CHANGE_ROLE` | User | Cambiar rol | Cambiar el rol de una membresía. |
| `P-USER_DEACTIVATE` | User | Dar de baja | Baja lógica. |
| `P-USER_REACTIVATE` | User | Reactivar | Reactivación con nuevo blanqueo. |
| `P-USER_RESET_PASSWORD` | User | Blanquear contraseña | Blanqueo administrativo. |
| `P-USER_VIEW_ASSIGNABLE` | User | Ver asignables | Ver candidatos a responsable del área. |
| `P-ROLE_VIEW` | Role | Ver | Ver catálogo de roles. |
| `P-ROLE_MANAGE` | Role | Gestionar | **Reservado**; no se expone CRUD. |
| `P-DIRECTION_VIEW` | Direction | Ver | Ver direcciones. |
| `P-DIRECTION_MANAGE` | Direction | Gestionar | Crear/editar/activar/desactivar direcciones. |
| `P-AREA_VIEW` | Area | Ver | Ver áreas. |
| `P-AREA_MANAGE` | Area | Gestionar | Crear/editar/activar/desactivar áreas. |
| `P-TICKET_VIEW` | Ticket | Ver | Ver tickets dentro del alcance. |
| `P-TICKET_VIEW_DELETED` | Ticket | Ver eliminados | Ver tickets eliminados dentro del alcance. |
| `P-TICKET_CREATE` | Ticket | Crear | Crear tickets. |
| `P-TICKET_EDIT` | Ticket | Editar | Editar tickets abiertos dentro del alcance. |
| `P-TICKET_ASSIGN` | Ticket | Asignar | Asignar responsable. |
| `P-TICKET_REASSIGN` | Ticket | Reasignar | Cambiar responsable existente. |
| `P-TICKET_TAKE` | Ticket | Tomar | Autoasignarse un ticket sin asignar. |
| `P-TICKET_START` | Ticket | Iniciar | `PENDING` → `IN_PROGRESS`. |
| `P-TICKET_COMPLETE` | Ticket | Completar | `IN_PROGRESS` → `COMPLETED`. |
| `P-TICKET_CANCEL` | Ticket | Cancelar | `PENDING`/`IN_PROGRESS` → `CANCELLED`. |
| `P-TICKET_DELETE` | Ticket | Eliminar | Marca de eliminación lógica. |
| `P-TICKET_RESTORE` | Ticket | Restaurar | Quitar marca de eliminación. |
| `P-TICKET_REOPEN` | Ticket | Reabrir | `COMPLETED`/`CANCELLED` → `PENDING`/`IN_PROGRESS`. |
| `P-TICKET_VIEW_HISTORY` | Ticket | Ver historial | Ver historial funcional del ticket. |
| `P-TICKET_EXPORT` | Ticket | Exportar | Exportar listados. |
| `P-RECEIPT_VIEW` | Receipt | Ver | Ver metadatos del comprobante. |
| `P-RECEIPT_DOWNLOAD` | Receipt | Descargar | Descargar el PDF. |
| `P-RECEIPT_UPLOAD` | Receipt | Subir | Adjuntar comprobante al completar. |
| `P-STATISTICS_VIEW` | Statistics | Ver | Ver estadísticas dentro del alcance. |
| `P-STATISTICS_VIEW_GLOBAL` | Statistics | Ver global | Ver estadísticas globales. |
| `P-STATISTICS_EXPORT` | Statistics | Exportar | Exportar reportes. |
| `P-AUDIT_VIEW` | Audit | Ver | Ver auditoría de negocio dentro del alcance. |
| `P-AUDIT_VIEW_GLOBAL` | Audit | Ver global | Ver auditoría de negocio global. |
| `P-PROFILE_VIEW` | Profile | Ver | Ver perfil propio. |
| `P-PROFILE_EDIT` | Profile | Editar | Editar nombre, apellido, correo y contraseña propios. |
| `P-PROFILE_CHANGE_PASSWORD` | Profile | Cambiar contraseña | Cambiar contraseña propia. |
| `P-SETTINGS_MANAGE` | Settings | Gestionar | Configurar la zona horaria. |

### 4.2 Permisos reservados

`P-ROLE_MANAGE` existe en el catálogo para futuras versiones, pero no se asigna a ningún rol operativo ni se expone en la API inicial (D-17). Es la denominación en inglés de `P-ROL-GESTIONAR`, citada por la decisión.

## 5. Alcance de datos

| Código | Alcance | Aplica a |
|---|---|---|
| `S-GLOBAL` | Todos los datos. | `SUPERUSER`. |
| `S-AREA` | Datos de las áreas donde el usuario tiene membresía activa con el rol correspondiente. | `ADMINISTRATIVE`, `AREA_MANAGER`. |
| `S-OWN_CREATED` | Tickets creados por el propio usuario. | `ADMINISTRATIVE` para edición. |
| `S-AGENT_QUEUE` | Tickets asignados al usuario + tickets sin asignar de las áreas donde es `AGENT`. | `AGENT`. |
| `S-OWN_ASSIGNED` | Tickets asignados al propio usuario. | `AGENT` para transiciones. |
| `S-SELF` | Perfil propio. | Todos. |

### 5.1 Reglas de alcance

1. `SUPERUSER` siempre `S-GLOBAL`.
2. `ADMINISTRATIVE` y `AREA_MANAGER` ven datos de sus áreas; la unión de áreas aplica si tienen varias membresías.
3. `ADMINISTRATIVE` edita sólo tickets que creó (`S-OWN_CREATED`), aunque estén en su área.
4. `AREA_MANAGER` edita cualquier ticket de su área (`S-AREA`).
5. `AGENT` ve asignados y sin asignar de sus áreas; sólo transiciona los asignados a él.
6. Los tickets eliminados se ven según `P-TICKET_VIEW_DELETED`: `SUPERUSER` global; `ADMINISTRATIVE` y `AREA_MANAGER` en su área; `AGENT` no.
7. Estadísticas de negocio: `SUPERUSER` global; `ADMINISTRATIVE`/`AREA_MANAGER` por área; `AGENT` sin acceso. Auditoría de negocio: `SUPERUSER` global; `ADMINISTRATIVE`/`AREA_MANAGER` por área; `AGENT` sólo sobre sus tickets asignados.
8. La dirección se deriva de `Area.direction_id`; no se consulta una dirección afectada separada (D-29).
9. El alcance se evalúa en el backend en cada operación, no sólo en la UI.

## 6. Matriz de permisos

Leyenda: **Sí** = aprobado; **No** = denegado; **Reservado** = existe pero no se asigna.

| Permiso | `SUPERUSER` | `ADMINISTRATIVE` | `AREA_MANAGER` | `AGENT` |
|---|---|---|---|---|
| `P-USER_VIEW` | Sí | No | No | No |
| `P-USER_CREATE` | Sí | No | No | No |
| `P-USER_EDIT` | Sí | No | No | No |
| `P-USER_CHANGE_ROLE` | Sí | No | No | No |
| `P-USER_DEACTIVATE` | Sí | No | No | No |
| `P-USER_REACTIVATE` | Sí | No | No | No |
| `P-USER_RESET_PASSWORD` | Sí | No | No | No |
| `P-USER_VIEW_ASSIGNABLE` | Sí | Sí (área) | Sí (área) | No |
| `P-ROLE_VIEW` | Sí | Sí | Sí | Sí |
| `P-ROLE_MANAGE` | Reservado | No | No | No |
| `P-DIRECTION_VIEW` | Sí (global) | Sí (dirección de sus áreas) | Sí (dirección de sus áreas) | Sí (dirección de sus áreas) |
| `P-DIRECTION_MANAGE` | Sí | No | No | No |
| `P-AREA_VIEW` | Sí (global) | Sí (áreas propias) | Sí (áreas propias) | Sí (áreas propias) |
| `P-AREA_MANAGE` | Sí | No | No | No |
| `P-TICKET_VIEW` | Sí (global) | Sí (área) | Sí (área) | Sí (cola del área) |
| `P-TICKET_VIEW_DELETED` | Sí (global) | Sí (área) | Sí (área) | No |
| `P-TICKET_CREATE` | Sí | Sí | Sí | No |
| `P-TICKET_EDIT` | Sí | Sí (`S-OWN_CREATED`) | Sí (área) | No |
| `P-TICKET_ASSIGN` | Sí | Sí (área) | Sí (área) | No |
| `P-TICKET_REASSIGN` | Sí | No | Sí (área) | No |
| `P-TICKET_TAKE` | Sí (si es elegible) | No | No | Sí (sin asignar) |
| `P-TICKET_START` | Sí | Sí (área) | Sí (área) | Sí (propio) |
| `P-TICKET_COMPLETE` | Sí | No | Sí (área) | Sí (propio) |
| `P-TICKET_CANCEL` | Sí | No | Sí (área) | Sí (propio) |
| `P-TICKET_DELETE` | Sí | No | Sí (área) | No |
| `P-TICKET_RESTORE` | Sí | No | Sí (área) | No |
| `P-TICKET_REOPEN` | Sí | Sí (área) | Sí (área) | No |
| `P-TICKET_VIEW_HISTORY` | Sí | Sí (área) | Sí (área) | Sí (propio) |
| `P-TICKET_EXPORT` | Sí | Sí (área) | Sí (área) | No |
| `P-RECEIPT_VIEW` | Sí | Sí (área) | Sí (área) | Sí (propio) |
| `P-RECEIPT_DOWNLOAD` | Sí | Sí (área) | Sí (área) | Sí (propio) |
| `P-RECEIPT_UPLOAD` | Sí | No | Sí (área) | Sí (propio) |
| `P-STATISTICS_VIEW` | Sí (global) | Sí (área) | Sí (área) | No |
| `P-STATISTICS_VIEW_GLOBAL` | Sí | No | No | No |
| `P-STATISTICS_EXPORT` | Sí | Sí (área) | Sí (área) | No |
| `P-AUDIT_VIEW` | Sí (global) | Sí (área) | Sí (área) | Sí (propios) |
| `P-AUDIT_VIEW_GLOBAL` | Sí | No | No | No |
| `P-PROFILE_VIEW` | Sí | Sí | Sí | Sí |
| `P-PROFILE_EDIT` | Sí | Sí | Sí | Sí |
| `P-PROFILE_CHANGE_PASSWORD` | Sí | Sí | Sí | Sí |
| `P-SETTINGS_MANAGE` | Sí | No | No | No |

### 6.1 Notas de la matriz

1. `SUPERUSER` tiene todos los permisos operativos, incluido `P-SETTINGS_MANAGE`; `P-ROLE_MANAGE` permanece reservado.
2. `ADMINISTRATIVE` no puede reasignar: D-05 sólo le otorga “asignar”. La reasignación es de `AREA_MANAGER`.
3. `ADMINISTRATIVE` no puede completar, cancelar, eliminar ni restaurar: D-05/D-31 no lo autorizan.
4. `ADMINISTRATIVE` sí puede iniciar y reabrir tickets de su área (D-31/D-30).
5. `AREA_MANAGER` puede iniciar, completar, cancelar, eliminar, restaurar y reabrir tickets de su área (D-05/D-31).
6. `AGENT` puede iniciar, completar y cancelar sus propios tickets (D-12), pero no los de otros.
7. `AGENT` no crea ni edita tickets.
8. `AGENT` no accede a estadísticas ni listado de eliminados; puede consultar auditoría de sus tickets asignados.
9. `P-TICKET_VIEW_DELETED` se habilita para `ADMINISTRATIVE` y `AREA_MANAGER` en su área por D-35 y D-03.
10. `P-ROLE_MANAGE` no se asigna (D-17).
11. `P-DIRECTION_VIEW` para roles no administradores se limita a la dirección derivada de sus áreas; no expone el catálogo global.
12. `P-AREA_VIEW` para roles no administradores se limita a las áreas propias; no habilita el catálogo global.
13. `P-TICKET_TAKE` para `SUPERUSER` sólo aplica si además tiene una membresía `AGENT` o `AREA_MANAGER` en el área; en caso contrario debe asignar.
14. `P-PROFILE_EDIT` no permite cambiar rol, áreas, legajo ni `username`; sólo nombre, apellido, correo y contraseña (D-33).
15. `P-AUDIT_VIEW` sólo expone auditoría de negocio; los logs de seguridad no se consultan por este permiso (D-39). `AGENT` la usa sólo sobre sus tickets asignados.

## 7. Reglas por rol

### 7.1 `SUPERUSER`

- Alcance global.
- Gestiona usuarios, membresías y roles (Q-024, D-33).
- Gestiona áreas, direcciones y la zona horaria (Q-004, D-14, D-37).
- Puede crear, ver, editar, asignar, reasignar, tomar, iniciar, completar, cancelar, eliminar, restaurar y reabrir tickets.
- Puede ver historial, comprobantes, estadísticas globales y auditoría de negocio global.
- No puede desactivarse a sí mismo si es el último `SUPERUSER` activo, ni quitarse el rol, ni permitir que el sistema quede sin `SUPERUSER` activo (D-36).

### 7.2 `ADMINISTRATIVE`

- Alcance por área.
- Crea tickets en sus áreas.
- Edita sólo tickets creados por él (`S-OWN_CREATED`).
- Asigna tickets de sus áreas; no reasigna.
- Inicia y reabre tickets de sus áreas (D-31/D-30).
- No completa, cancela, elimina ni restaura.
- Ve historial y comprobantes de sus áreas.
- Ve estadísticas y auditoría de negocio de sus áreas.
- No gestiona usuarios, catálogos ni configuración.

### 7.3 `AREA_MANAGER`

- Alcance por área.
- Crea y edita tickets de sus áreas.
- Asigna y reasigna tickets de sus áreas.
- Inicia, completa, cancela, elimina, restaura y reabre tickets de sus áreas (D-31/D-30).
- Ve historial y comprobantes de sus áreas.
- Ve estadísticas y auditoría de negocio de sus áreas.
- No gestiona usuarios, catálogos ni configuración.

### 7.4 `AGENT`

- Alcance `S-AGENT_QUEUE`: asignados a él + sin asignar de sus áreas.
- Puede tomar tickets sin asignar de sus áreas.
- Puede iniciar, completar y cancelar los tickets asignados a él.
- No crea, edita, asigna, reasigna, elimina, restaura ni reabre.
- Ve historial y comprobantes de sus tickets.
- No accede a estadísticas ni listado de eliminados.
- Puede consultar auditoría de sus tickets asignados; no accede a auditoría global ni de área.
- No gestiona usuarios, catálogos ni configuración.

## 8. Elegibilidad del responsable

Un usuario puede ser responsable de un ticket si:

1. está `ACTIVE`;
2. tiene una membresía activa en el área del ticket;
3. el rol de esa membresía es `AGENT` o `AREA_MANAGER`.

Un `SUPERUSER` o `ADMINISTRATIVE` no es elegible como responsable salvo que también tenga una membresía `AGENT` o `AREA_MANAGER` en el área.

## 9. Baja y cambio de área del responsable

Si un usuario con tickets activos asignados se da de baja o cambia de área/rol:

1. el sistema bloquea la operación;
2. muestra los tickets activos afectados;
3. exige reasignar o desasignar;
4. registra la decisión en auditoría;
5. invalida sesiones por cambio de rol/área (D-07).

La opción de forzar la baja con desasignación automática no está aprobada; D-06 pide bloqueo con aviso explícito.

## 10. Verificación de autorización

| Escenario | Resultado esperado |
|---|---|
| `AGENT` intenta ver un ticket de otra área | Denegado. |
| `AGENT` intenta cancelar un ticket asignado a otro | Denegado. |
| `AGENT` toma un ticket sin asignar de su área | Permitido. |
| `AGENT` inicia un ticket que acaba de tomar | Permitido. |
| `ADMINISTRATIVE` edita un ticket creado por otro en su área | Denegado. |
| `ADMINISTRATIVE` edita un ticket creado por él | Permitido. |
| `ADMINISTRATIVE` reasigna | Denegado (D-05 sólo asigna). |
| `ADMINISTRATIVE` completa un ticket | Denegado (D-31). |
| `ADMINISTRATIVE` reabre un ticket de su área | Permitido (D-30/D-31). |
| `AREA_MANAGER` edita un ticket de su área | Permitido. |
| `AREA_MANAGER` elimina un ticket de su área | Permitido. |
| `AREA_MANAGER` restaura un ticket de su área | Permitido (D-31). |
| `AREA_MANAGER` reabre un ticket de su área | Permitido (D-30/D-31). |
| `SUPERUSER` opera sobre cualquier área | Permitido. |
| Usuario con `AREA_MANAGER` en Área 1 y `AGENT` en Área 2 intenta editar en Área 2 | Denegado como Agente. |
| Usuario con `SUPERUSER` en cualquier área | Global. |
| `AGENT` intenta ver auditoría de un ticket ajeno | Denegado. |
| `ADMINISTRATIVE` intenta cambiar una configuración | Denegado. |
| Último `SUPERUSER` activo intenta darse de baja o quitarse el rol | Denegado (D-36). |

## 11. Decisiones incorporadas

No quedan decisiones pendientes que afecten permisos. Las decisiones D-29 a D-39 están reflejadas en este documento; los riesgos residuales están en [`open-decisions.md`](./open-decisions.md).

## 12. Trazabilidad

| Decisión | Dónde se refleja |
|---|---|
| D-02 | §2, §6, §7. |
| D-03 | §5, §6, §7. |
| D-05 | §6, §7, §8. |
| D-06 | §9. |
| D-07 | §3.2, §9. |
| D-12 | §6, §7.4. |
| D-17 | §2, §4.2, §6. |
| D-21 | §3, §5. |
| D-29 | §1, §5.1, §6.1. |
| D-30 | §1, §6, §7. |
| D-31 | §1, §6, §7, §10. |
| D-33 | §1, §3.2, §6.1, §7.1. |
| D-35 | §1, §5.1, §6.1. |
| D-36 | §3.2, §7.1, §10. |
| D-39 | §1, §5.1, §6.1. |
| Q-002 | §5.1, §10. |
| Q-005 | §5. |
| Q-024 | §7.1, §3.2. |
