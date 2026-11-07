# Casos de uso

> **Fase 1 — Revisión 4.** Casos de uso actualizados a `Ticket`, tipos `INCIDENT`/`TASK`, cuatro roles, multi-área, cierres históricos, reapertura y trazabilidad de eliminaciones/restauraciones.
> Los permisos y alcances se detallan en [`roles-and-permissions.md`](./roles-and-permissions.md); las transiciones en [`task-lifecycle.md`](./task-lifecycle.md).
> Las decisiones D-01 a D-39 están incorporadas; no quedan casos pendientes.

## Índice

- [A. Autenticación y perfil](#a-autenticación-y-perfil)
  - [UC-AUT-01 — Iniciar sesión](#uc-aut-01--iniciar-sesión)
  - [UC-AUT-02 — Cerrar sesión](#uc-aut-02--cerrar-sesión)
  - [UC-AUT-03 — Renovar o expirar sesión](#uc-aut-03--renovar-o-expirar-sesión)
  - [UC-AUT-04 — Consultar perfil propio](#uc-aut-04--consultar-perfil-propio)
  - [UC-AUT-05 — Editar perfil propio](#uc-aut-05--editar-perfil-propio)
  - [UC-AUT-06 — Cambiar contraseña propia](#uc-aut-06--cambiar-contraseña-propia)
- [B. Administración](#b-administración)
  - [UC-ADM-01 — Crear usuario](#uc-adm-01--crear-usuario)
  - [UC-ADM-02 — Editar usuario y membresías](#uc-adm-02--editar-usuario-y-membresías)
  - [UC-ADM-03 — Cambiar rol de una membresía](#uc-adm-03--cambiar-rol-de-una-membresía)
  - [UC-ADM-04 — Dar de baja usuario](#uc-adm-04--dar-de-baja-usuario)
  - [UC-ADM-05 — Reactivar usuario](#uc-adm-05--reactivar-usuario)
  - [UC-ADM-06 — Blanquear contraseña](#uc-adm-06--blanquear-contraseña)
  - [UC-ADM-07 — Consultar roles y permisos](#uc-adm-07--consultar-roles-y-permisos)
  - [UC-ADM-08 — Gestionar direcciones](#uc-adm-08--gestionar-direcciones)
  - [UC-ADM-09 — Gestionar áreas](#uc-adm-09--gestionar-áreas)
  - [UC-ADM-10 — Consultar auditoría](#uc-adm-10--consultar-auditoría)
  - [UC-ADM-11 — Consultar panel global](#uc-adm-11--consultar-panel-global)
  - [UC-ADM-12 — Gestionar configuración](#uc-adm-12--gestionar-configuración)
- [C. Tickets](#c-tickets)
  - [UC-TKT-01 — Crear ticket](#uc-tkt-01--crear-ticket)
  - [UC-TKT-02 — Consultar listado de tickets](#uc-tkt-02--consultar-listado-de-tickets)
  - [UC-TKT-03 — Ver detalle de ticket](#uc-tkt-03--ver-detalle-de-ticket)
  - [UC-TKT-04 — Editar ticket](#uc-tkt-04--editar-ticket)
  - [UC-TKT-05 — Asignar o reasignar responsable](#uc-tkt-05--asignar-o-reasignar-responsable)
  - [UC-TKT-06 — Tomar ticket sin asignar](#uc-tkt-06--tomar-ticket-sin-asignar)
  - [UC-TKT-07 — Iniciar ticket](#uc-tkt-07--iniciar-ticket)
  - [UC-TKT-08 — Completar ticket](#uc-tkt-08--completar-ticket)
  - [UC-TKT-09 — Cancelar ticket](#uc-tkt-09--cancelar-ticket)
  - [UC-TKT-10 — Eliminar ticket](#uc-tkt-10--eliminar-ticket)
  - [UC-TKT-11 — Restaurar ticket](#uc-tkt-11--restaurar-ticket)
  - [UC-TKT-12 — Reabrir ticket](#uc-tkt-12--reabrir-ticket)
  - [UC-TKT-13 — Descargar comprobante](#uc-tkt-13--descargar-comprobante)
  - [UC-TKT-14 — Consultar historial de ticket](#uc-tkt-14--consultar-historial-de-ticket)
- [D. Reportes y auditoría de área](#d-reportes-y-auditoría-de-área)
  - [UC-REP-01 — Consultar estadísticas](#uc-rep-01--consultar-estadísticas)
  - [UC-REP-02 — Exportar listado o reporte](#uc-rep-02--exportar-listado-o-reporte)
  - [UC-REP-03 — Consultar auditoría de área](#uc-rep-03--consultar-auditoría-de-área)

---

## A. Autenticación y perfil

### UC-AUT-01 — Iniciar sesión

| Campo | Valor |
|---|---|
| **Actor** | Usuario activo. |
| **Objetivo** | Acceder al sistema con credenciales válidas. |
| **Precondiciones** | El usuario existe y está `ACTIVE`; correo obligatorio e interno. |
| **Permiso** | Público. |
| **Alcance** | No aplica. |

**Flujo principal:**

1. El usuario ingresa su identificador (usuario o correo) y contraseña (D-38).
2. El backend normaliza el identificador y busca al usuario por `username` o `email`.
3. Verifica la contraseña contra el hash.
4. Verifica que el usuario esté `ACTIVE`.
5. Crea la sesión de servidor y emite cookie `HttpOnly`.
6. Registra `last_login_at` (D-38).
7. Devuelve identidad, membresías, roles y permisos efectivos.
8. El frontend redirige al panel.

**Flujos alternativos:**

- A1. Credenciales inválidas: mensaje genérico, sin revelar si el usuario existe.
- A2. Usuario `INACTIVE`: se rechaza con el mismo mensaje genérico.
- A3. Usuario sin membresía activa: se rechaza; no tiene alcance.
- A4. Rate limiting o bloqueo temporal: se aplica según D-23.

**Errores y restricciones:**

- `401` por credenciales inválidas o usuario inactivo.
- La sesión expira por inactividad deslizante a los 60 minutos (D-07).
- No se permite login de usuarios dados de baja (Q-001).
- Se registra `last_login_at` en cada login exitoso (D-38).

**Reglas:** RN-011, RN-012, RN-015, RN-016, RN-019.

---

### UC-AUT-02 — Cerrar sesión

| Campo | Valor |
|---|---|
| **Actor** | Usuario autenticado. |
| **Objetivo** | Finalizar la sesión de forma segura. |
| **Precondiciones** | Sesión activa. |
| **Permiso** | Autenticado. |
| **Alcance** | Propio. |

**Flujo principal:**

1. El usuario solicita cerrar sesión.
2. El backend invalida la sesión de servidor.
3. El frontend elimina el estado local y redirige al login.

**Flujos alternativos:**

- A1. La sesión ya expiró: se responde como cierre exitoso o `401`, según el contrato.

**Errores y restricciones:** no se requiere confirmación adicional. Logout es control de seguridad, no regla de negocio.

**Reglas:** RN-011.

---

### UC-AUT-03 — Renovar o expirar sesión

| Campo | Valor |
|---|---|
| **Actor** | Usuario autenticado / sistema. |
| **Objetivo** | Mantener la sesión activa mientras hay actividad y expirarla por inactividad. |
| **Precondiciones** | Sesión existente. |
| **Permiso** | Autenticado. |
| **Alcance** | Propio. |

**Flujo principal:**

1. Cada operación autenticada renueva la marca de actividad.
2. Si la inactividad supera 60 minutos, el backend invalida la sesión.
3. El frontend recibe `401` y redirige al login con un mensaje de sesión expirada.

**Flujos alternativos:**

- A1. Cambio de rol/área: la sesión se invalida (D-07).
- A2. Baja del usuario: la sesión se invalida (D-07).
- A3. Blanqueo de contraseña: las sesiones se invalidan (D-07).

**Errores y restricciones:** no hay expiración absoluta aprobada; sólo la deslizante de 60 minutos (D-07).

**Reglas:** RN-012, RN-013.

---

### UC-AUT-04 — Consultar perfil propio

| Campo | Valor |
|---|---|
| **Actor** | Usuario autenticado. |
| **Objetivo** | Ver los datos propios. |
| **Precondiciones** | Sesión activa. |
| **Permiso** | `P-PROFILE_VIEW`. |
| **Alcance** | `S-SELF`. |

**Flujo principal:**

1. El usuario abre su perfil.
2. El backend devuelve legajo, nombre, apellido, usuario, correo y membresías con rol/área.
3. No devuelve hash ni datos sensibles.
4. El último acceso (`last_login_at`) se incluye (D-38).

**Flujos alternativos:** ninguno relevante.

**Errores y restricciones:** `401` sin sesión.

**Reglas:** RN-021.

---

### UC-AUT-05 — Editar perfil propio

| Campo | Valor |
|---|---|
| **Actor** | Usuario autenticado. |
| **Objetivo** | Actualizar datos personales y de contacto. |
| **Precondiciones** | Sesión activa; usuario localizado por legajo. |
| **Permiso** | `P-PROFILE_EDIT`. |
| **Alcance** | `S-SELF`. |

**Flujo principal:**

1. El usuario modifica nombre, apellido, correo y contraseña (D-33).
2. El backend valida formato, unicidad y dominio interno del correo.
3. Actualiza el registro localizado por legajo.
4. Audita el cambio.
5. Devuelve los datos actualizados.

**Flujos alternativos:**

- A1. Usuario o correo duplicado: `409`.
- A2. Intento de cambiar rol, área o legajo: se rechaza.
- A3. Correo vacío: `422`; el correo es obligatorio (D-16).

**Errores y restricciones:** el legajo es inmutable (D-15); el rol/área se cambia por administración (D-21).

**Reglas:** RN-016, RN-017, RN-018, RN-021.

---

### UC-AUT-06 — Cambiar contraseña propia

| Campo | Valor |
|---|---|
| **Actor** | Usuario autenticado. |
| **Objetivo** | Cambiar la propia contraseña. |
| **Precondiciones** | Sesión activa; conoce la contraseña actual (control de seguridad). |
| **Permiso** | `P-PROFILE_CHANGE_PASSWORD`. |
| **Alcance** | `S-SELF`. |

**Flujo principal:**

1. El usuario ingresa la contraseña actual y la nueva, con confirmación.
2. El backend valida coincidencia y política configurable.
3. Hashea y guarda la nueva contraseña.
4. Audita el cambio.
5. La invalidación de otras sesiones se define en Fase 2; no está aprobada.

**Flujos alternativos:**

- A1. Contraseña actual incorrecta: `401`/`422`.
- A2. Nueva contraseña no cumple la política: `422`.
- A3. Confirmación distinta: `422`.

**Errores y restricciones:** la política es configurable (D-23); la contraseña actual se valida como control de seguridad.

**Reglas:** RN-019, RN-020.

---

## B. Administración

### UC-ADM-01 — Crear usuario

| Campo | Valor |
|---|---|
| **Actor** | Administrador. |
| **Objetivo** | Incorporar un usuario con una o más membresías. |
| **Precondiciones** | Áreas y roles existentes y activos; datos obligatorios completos. |
| **Permiso** | `P-USER_CREATE`. |
| **Alcance** | `S-GLOBAL`. |

**Flujo principal:**

1. El Administrador ingresa legajo, nombre, apellido, usuario, correo, contraseña y una o más membresías (`area_id` + rol).
2. El backend valida formato, unicidad de legajo/usuario/correo, correo interno y existencia de áreas/roles.
3. Hashea la contraseña.
4. Crea el usuario `ACTIVE` y sus `UserAreaMembership`.
5. Audita el alta.
6. Devuelve el usuario creado.

**Flujos alternativos:**

- A1. Legajo, usuario o correo duplicado: `409`.
- A2. Área/rol inexistente o inactivo: `422`.
- A3. Correo externo: `422` (D-16).
- A4. Sin membresías: `422`.
- A5. Dos membresías para la misma área: `422`.

**Errores y restricciones:** el correo es obligatorio e interno (D-16); el legajo es inmutable (D-15); un rol por área (D-21).

**Reglas:** RN-016, RN-017, RN-018, RN-030, RN-037.

---

### UC-ADM-02 — Editar usuario y membresías

| Campo | Valor |
|---|---|
| **Actor** | Administrador. |
| **Objetivo** | Corregir datos y membresías de un usuario. |
| **Precondiciones** | Usuario existente. |
| **Permiso** | `P-USER_EDIT`. |
| **Alcance** | `S-GLOBAL`. |

**Flujo principal:**

1. El Administrador modifica nombre, apellido, correo, usuario y membresías.
2. El backend valida unicidad, correo interno y referencias.
3. Actualiza y audita.
4. Si cambia un área/rol y el usuario tiene tickets activos asignados, exige reasignación o desasignación (D-06).
5. Invalida sesiones por cambio de rol/área (D-07).

**Flujos alternativos:**

- A1. Duplicados: `409`.
- A2. Cambio incompatible con asignaciones: `409`.
- A3. Intento de cambiar legajo: rechazado (D-15).
- A4. Intento de dejar al usuario sin membresías: `422`.

**Errores y restricciones:** el rol se cambia por membresía; no se edita el legajo.

**Reglas:** RN-013, RN-016, RN-017, RN-018, RN-037.

---

### UC-ADM-03 — Cambiar rol de una membresía

| Campo | Valor |
|---|---|
| **Actor** | Administrador. |
| **Objetivo** | Asignar un rol distinto en un área. |
| **Precondiciones** | Usuario, área y rol existentes. |
| **Permiso** | `P-USER_CHANGE_ROLE`. |
| **Alcance** | `S-GLOBAL`. |

**Flujo principal:**

1. El Administrador selecciona usuario, área y nuevo rol.
2. El backend valida que el cambio no deje al sistema sin `SUPERUSER` activo (D-36).
3. Valida compatibilidad con tickets asignados (D-06).
4. Actualiza la membresía, audita e invalida sesiones.
5. El cambio rige de inmediato.

**Flujos alternativos:**

- A1. Último `SUPERUSER` activo: `409` (D-36).
- A2. Tickets asignados incompatibles: `409` con instrucción de reasignar.
- A3. Rol inexistente: `422`.
- A4. Área sin membresía: `404`.

**Errores y restricciones:** un solo rol por área (D-21).

**Reglas:** RN-013, RN-039, RN-040.

---

### UC-ADM-04 — Dar de baja usuario

| Campo | Valor |
|---|---|
| **Actor** | Administrador. |
| **Objetivo** | Impedir el acceso sin borrar historial. |
| **Precondiciones** | Usuario activo; motivo informado. |
| **Permiso** | `P-USER_DEACTIVATE`. |
| **Alcance** | `S-GLOBAL`. |

**Flujo principal:**

1. El Administrador selecciona el usuario e ingresa el motivo.
2. El backend valida que no sea el último `SUPERUSER` activo ni él mismo (D-36).
3. Valida tickets activos asignados.
4. Marca al usuario `INACTIVE`, guarda motivo y fecha.
5. Invalida sus sesiones.
6. Audita la baja.

**Flujos alternativos:**

- A1. Tickets activos asignados: `409`; se debe reasignar o desasignar (D-06).
- A2. Último `SUPERUSER` activo o auto-baja: `409` (D-36).
- A3. Usuario ya inactivo: `409`.

**Errores y restricciones:** los tickets del usuario siguen visibles (Q-014).

**Reglas:** RN-013, RN-015, RN-067.

---

### UC-ADM-05 — Reactivar usuario

| Campo | Valor |
|---|---|
| **Actor** | Administrador. |
| **Objetivo** | Devolver el acceso a un usuario inactivo. |
| **Precondiciones** | Usuario inactivo; membresías válidas; nueva contraseña. |
| **Permiso** | `P-USER_REACTIVATE`. |
| **Alcance** | `S-GLOBAL`. |

**Flujo principal:**

1. El Administrador selecciona el usuario, define membresías y nueva contraseña.
2. El backend valida áreas/roles y unicidad vigente.
3. Marca al usuario `ACTIVE`, limpia motivo y registra fecha.
4. Hashea la nueva contraseña (D-07).
5. Audita la reactivación.

**Flujos alternativos:**

- A1. Área/rol inválidos: `422`.
- A2. Usuario ya activo: `409`.
- A3. Sin nueva contraseña: `422` (D-07).

**Errores y restricciones:** se exige nuevo blanqueo al reactivar (D-07).

**Reglas:** RN-014, RN-020.

---

### UC-ADM-06 — Blanquear contraseña

| Campo | Valor |
|---|---|
| **Actor** | Administrador. |
| **Objetivo** | Restablecer la contraseña de un usuario. |
| **Precondiciones** | Usuario existente; nueva contraseña y confirmación. |
| **Permiso** | `P-USER_RESET_PASSWORD`. |
| **Alcance** | `S-GLOBAL`. |

**Flujo principal:**

1. El Administrador selecciona el usuario e ingresa la nueva contraseña y su confirmación.
2. El backend valida coincidencia y política.
3. Hashea y guarda.
4. Invalida sesiones activas (D-07).
5. Audita el blanqueo.

**Flujos alternativos:**

- A1. Confirmación distinta: `422`.
- A2. Política incumplida: `422`.
- A3. Usuario inexistente: `404`.

**Errores y restricciones:** se usa la nueva contraseña, no el campo de repetición (Q-009).

**Reglas:** RN-013, RN-020.

---

### UC-ADM-07 — Consultar roles y permisos

| Campo | Valor |
|---|---|
| **Actor** | Administrador; lectura para los demás roles. |
| **Objetivo** | Consultar el catálogo fijo de roles y permisos. |
| **Precondiciones** | Sesión activa. |
| **Permiso** | `P-ROLE_VIEW`. |
| **Alcance** | `S-GLOBAL` o propio. |

**Flujo principal:**

1. El usuario consulta roles y permisos.
2. El backend devuelve el catálogo fijo.
3. No se permite crear, editar ni eliminar roles.

**Flujos alternativos:**

- A1. Intento de CRUD de roles: `405`/`403`; `P-ROLE_MANAGE` está reservado (D-17).

**Errores y restricciones:** catálogo fijo; no hay gestión dinámica.

**Reglas:** RN-030, RN-038.

---

### UC-ADM-08 — Gestionar direcciones

| Campo | Valor |
|---|---|
| **Actor** | Administrador. |
| **Objetivo** | Mantener el catálogo de direcciones. |
| **Precondiciones** | Sesión activa. |
| **Permiso** | `P-DIRECTION_MANAGE`. |
| **Alcance** | `S-GLOBAL`. |

**Flujo principal:**

1. El Administrador lista, crea o edita una dirección.
2. El backend valida código único entre activos, nombre y estado.
3. Guarda y audita.
4. Para dar de baja, valida que no haya áreas activas dependientes (D-18).
5. La baja es lógica.

**Flujos alternativos:**

- A1. Código duplicado entre activos: `409`.
- A2. Dirección con áreas activas: `409` (D-18).
- A3. Código inactivo reutilizado: permitido (D-19).
- A4. Reactivación: vuelve a estar disponible.

**Errores y restricciones:** no se borra físicamente (Q-018, D-18).

**Reglas:** RN-050, RN-052, RN-053, RN-054, RN-055.

---

### UC-ADM-09 — Gestionar áreas

| Campo | Valor |
|---|---|
| **Actor** | Administrador. |
| **Objetivo** | Mantener el catálogo de áreas. |
| **Precondiciones** | Dirección padre existente y activa. |
| **Permiso** | `P-AREA_MANAGE`. |
| **Alcance** | `S-GLOBAL`. |

**Flujo principal:**

1. El Administrador lista, crea o edita un área.
2. El backend valida nombre, `direction_id` y estado.
3. Guarda y audita.
4. Para dar de baja, valida usuarios y tickets activos (D-18).
5. La baja es lógica.

**Flujos alternativos:**

- A1. Dirección padre inactiva: `422`.
- A2. Área con usuarios/tickets activos: `409` o requiere reasignación (D-18).
- A3. Reactivación: vuelve a estar disponible.

**Errores y restricciones:** sólo el Administrador administra áreas (Q-004); cada área tiene `direction_id` (D-04).

**Reglas:** RN-050, RN-051, RN-053, RN-055.

---

### UC-ADM-10 — Consultar auditoría

| Campo | Valor |
|---|---|
| **Actor** | Administrador (global); Administrativo y Responsable (área). |
| **Objetivo** | Revisar cambios de negocio registrados. |
| **Precondiciones** | Sesión activa. |
| **Permiso** | `P-AUDIT_VIEW`; `P-AUDIT_VIEW_GLOBAL` para global. |
| **Alcance** | `S-GLOBAL` o `S-AREA`. |

**Flujo principal:**

1. El usuario aplica filtros por fecha, actor, entidad, acción o ticket.
2. El backend valida alcance.
3. Devuelve eventos paginados y ordenados.
4. El usuario puede ver el detalle de un evento.

**Flujos alternativos:**

- A1. Sin resultados: lista vacía.
- A2. Intento de ver otra área: denegado u oculto como `404`.
- A3. Exportación: sujeta a permiso; no se audita la exportación (D-22).

**Errores y restricciones:** la auditoría es de sólo lectura; no incluye lecturas ni descargas (D-22).

**Reglas:** RN-084 a RN-087.

---

### UC-ADM-11 — Consultar panel global

| Campo | Valor |
|---|---|
| **Actor** | Administrador. |
| **Objetivo** | Ver el estado global del sistema. |
| **Precondiciones** | Sesión activa. |
| **Permiso** | `P-STATISTICS_VIEW_GLOBAL`. |
| **Alcance** | `S-GLOBAL`. |

**Flujo principal:**

1. El Administrador abre el panel.
2. El backend devuelve totales por estado, eliminados, usuarios y áreas.
3. Cada tarjeta enlaza al listado correspondiente.
4. Los conteos respetan `reporting-and-files.md`.

**Flujos alternativos:**

- A1. Sin datos: ceros.
- A2. Usuarios inactivos: se excluyen de los listados operativos; pueden consultarse en la gestión de usuarios (D-33).

**Errores y restricciones:** los roles no administradores ven su panel de área (UC-REP-01).

**Reglas:** RN-090 a RN-098.

---

### UC-ADM-12 — Gestionar configuración

| Campo | Valor |
|---|---|
| **Actor** | Administrador. |
| **Objetivo** | Cambiar la zona horaria del sistema. |
| **Precondiciones** | Sesión activa. |
| **Permiso** | `P-SETTINGS_MANAGE`. |
| **Alcance** | `S-GLOBAL`. |

**Flujo principal:**

1. El Administrador consulta la configuración actual.
2. Cambia `TIMEZONE`; valor por defecto `America/Argentina/Buenos_Aires`.
3. El backend valida la zona horaria.
4. Guarda, audita y aplica a cálculos y presentación.

**Flujos alternativos:**

- A1. Zona inválida: `422`.
- A2. Intento de otro rol: `403`.

**Errores y restricciones:** `/settings` sólo expone `timezone` en la primera versión (D-37); no se agregan otros parámetros.

**Reglas:** RN-005, RN-100, RN-101.

---

## C. Tickets

### UC-TKT-01 — Crear ticket

| Campo | Valor |
|---|---|
| **Actor** | Administrador, Administrativo o Responsable de área. |
| **Objetivo** | Registrar un nuevo ticket. |
| **Precondiciones** | Área activa dentro del alcance; datos obligatorios completos. |
| **Permiso** | `P-TICKET_CREATE`. |
| **Alcance** | `S-GLOBAL` o `S-AREA`. |

**Flujo principal:**

1. El actor elige el tipo `INCIDENT` o `TASK`.
2. Ingresa `description`, `area_id`, `problem_date` y, opcionalmente, Nota Electrónica, afectado y celular (D-34). La dirección se deriva del área (D-29).
3. El backend valida datos, referencias activas y `problem_date`.
4. Genera número global, estado `PENDING` (D-34), `created_at` y `created_by_user_id`.
5. Valida que `problem_date` no sea futura para `INCIDENT`; para `TASK` sí se permite (D-10/D-32).
6. Crea historial y auditoría.
7. Devuelve el ticket creado.

**Flujos alternativos:**

- A1. Área inactiva o fuera de alcance: `422`/`403`.
- A2. Nota Electrónica inválida: `422` (D-09).
- A3. Datos faltantes: `422` (D-34).
- A4. `INCIDENT` con fecha futura: `422` (D-10).
- A5. `TASK` con fecha futura: permitido.

**Errores y restricciones:** el Agente no crea tickets (D-05/D-12).

**Reglas:** RN-003, RN-060, RN-061, RN-062, RN-066.

---

### UC-TKT-02 — Consultar listado de tickets

| Campo | Valor |
|---|---|
| **Actor** | Cualquier rol con `P-TICKET_VIEW`. |
| **Objetivo** | Encontrar tickets según estado, área, responsable, fechas o texto. |
| **Precondiciones** | Sesión activa. |
| **Permiso** | `P-TICKET_VIEW`; `P-TICKET_VIEW_DELETED` para eliminados. |
| **Alcance** | `S-GLOBAL`, `S-AREA` o `S-AGENT_QUEUE`. |

**Flujo principal:**

1. El usuario selecciona un listado o aplica filtros.
2. El backend aplica alcance y filtros.
3. Devuelve resultados paginados y ordenados.
4. El usuario puede abrir el detalle, exportar o ejecutar acciones permitidas.

**Flujos alternativos:**

- A1. Sin resultados: lista vacía.
- A2. Filtro inválido: `422`.
- A3. Intento de ver eliminados sin permiso: denegado u oculto.
- A4. Agente: ve asignados a él + sin asignar de sus áreas (D-03).

**Errores y restricciones:** listado actual = `PENDING` + `IN_PROGRESS`; completados, cancelados y eliminados se consultan por separado. Filtros server-side por tipo, área, responsable, creador y fechas (D-35).

**Reglas:** RN-033, RN-066, RN-094.

---

### UC-TKT-03 — Ver detalle de ticket

| Campo | Valor |
|---|---|
| **Actor** | Cualquier rol con alcance sobre el ticket. |
| **Objetivo** | Consultar todos los datos de un ticket. |
| **Precondiciones** | El ticket existe y está dentro del alcance. |
| **Permiso** | `P-TICKET_VIEW`; `P-TICKET_VIEW_DELETED` si está eliminado. |
| **Alcance** | Según rol. |

**Flujo principal:**

1. El usuario abre el ticket.
2. El backend devuelve datos generales, tipo, estado, responsable, comprobante (si existe) e historial resumido.
3. El usuario puede descargar el comprobante si tiene permiso.

**Flujos alternativos:**

- A1. Ticket inexistente o fuera de alcance: `404`.
- A2. Ticket eliminado sin permiso: `404`/`403`.

**Errores y restricciones:** no se exponen datos de otras áreas.

**Reglas:** RN-033, RN-066.

---

### UC-TKT-04 — Editar ticket

| Campo | Valor |
|---|---|
| **Actor** | Administrador; Administrativo si es creador; Responsable de área. |
| **Objetivo** | Corregir datos de un ticket abierto. |
| **Precondiciones** | Ticket en `PENDING` o `IN_PROGRESS`, no eliminado. |
| **Permiso** | `P-TICKET_EDIT`. |
| **Alcance** | `S-GLOBAL`, `S-OWN_CREATED` o `S-AREA`. |

**Flujo principal:**

1. El actor modifica los campos de negocio del ticket abierto: `type`, `description`, `electronic_note_id`, `affected_person_name`, `affected_phone`, `area_id` y `problem_date` (D-34).
2. El backend valida estado, referencias, `problem_date` y compatibilidad con el responsable.
3. Guarda y audita los cambios.
4. No cambia el estado.

**Flujos alternativos:**

- A1. Ticket completado/cancelado/eliminado: `409`.
- A2. Área nueva incompatible con el responsable: `409`.
- A3. `INCIDENT` con fecha futura: `422`.
- A4. Conflicto de versión: `409`.
- A5. Administrativo edita ticket ajeno: `403`.

**Errores y restricciones:** no se editan estado, creador, `created_at`, `ticket_number`, cierre, comprobante, cancelación ni eliminación (D-34).

**Reglas:** RN-061, RN-064, RN-066.

---

### UC-TKT-05 — Asignar o reasignar responsable

| Campo | Valor |
|---|---|
| **Actor** | Administrador; Administrativo (asignar); Responsable de área (asignar/reasignar). |
| **Objetivo** | Definir quién ejecutará el ticket. |
| **Precondiciones** | Ticket `PENDING` o `IN_PROGRESS`, no eliminado; usuario destino activo y elegible. |
| **Permiso** | `P-TICKET_ASSIGN`; `P-TICKET_REASSIGN` para cambiar responsable existente. |
| **Alcance** | `S-GLOBAL` o `S-AREA`. |

**Flujo principal:**

1. El actor selecciona un usuario elegible del área o deja el ticket sin responsable.
2. El backend valida estado, actividad, rol, membresía y área.
3. Actualiza responsable, fecha y actor.
4. Registra historial y auditoría.
5. La asignación no cambia el estado.

**Flujos alternativos:**

- A1. Usuario inactivo: `422`.
- A2. Usuario fuera del área o sin rol `AGENT`/`AREA_MANAGER`: `422`.
- A3. Ticket completado/cancelado/eliminado: `409`.
- A4. Administrativo intenta reasignar: `403`.
- A5. Conflicto de versión: `409`.

**Errores y restricciones:** el responsable debe estar activo, pertenecer al área y tener rol `AGENT` o `AREA_MANAGER` (D-05).

**Reglas:** RN-034, RN-035, RN-040, RN-043.

---

### UC-TKT-06 — Tomar ticket sin asignar

| Campo | Valor |
|---|---|
| **Actor** | Agente con membresía activa; Administrador sólo si además es elegible como Agente o Responsable de área en el área. |
| **Objetivo** | Autoasignarse un ticket sin responsable de su área. |
| **Precondiciones** | Ticket sin asignar, `PENDING` o `IN_PROGRESS`, no eliminado; actor con membresía activa en el área. |
| **Permiso** | `P-TICKET_TAKE`. |
| **Alcance** | `S-AGENT_QUEUE`. |

**Flujo principal:**

1. El Agente selecciona un ticket sin asignar de su área.
2. El backend valida que siga sin asignar y que el actor sea elegible.
3. Asigna al actor, registra fecha y actor.
4. Crea historial y auditoría.

**Flujos alternativos:**

- A1. Ticket ya asignado: `409`.
- A2. Ticket de otra área: `403`.
- A3. Ticket cerrado o eliminado: `409`.
- A4. Conflicto de versión: `409`.

**Errores y restricciones:** la toma no cambia el estado.

**Reglas:** RN-033, RN-036, RN-043.

---

### UC-TKT-07 — Iniciar ticket

| Campo | Valor |
|---|---|
| **Actor** | Administrador; Administrativo en su área; Responsable de área en su área; Agente asignado. |
| **Objetivo** | Pasar el ticket a `IN_PROGRESS`. |
| **Precondiciones** | Ticket `PENDING`, no eliminado. |
| **Permiso** | `P-TICKET_START`. |
| **Alcance** | `S-GLOBAL`, `S-AREA` o `S-OWN_ASSIGNED`. |

**Flujo principal:**

1. El actor solicita iniciar.
2. El backend valida estado, alcance y versión.
3. Cambia a `IN_PROGRESS`, registra fecha y actor en historial.
4. Crea auditoría.

**Flujos alternativos:**

- A1. Estado distinto de `PENDING`: `409`.
- A2. Agente no asignado: `403`.
- A3. Ticket eliminado: `409`.
- A4. Responsable inactivo: `409`; puede iniciarse sin responsable (D-05).

**Errores y restricciones:** iniciar no exige responsable (D-05).

**Reglas:** RN-041, RN-081, RN-082.

---

### UC-TKT-08 — Completar ticket

| Campo | Valor |
|---|---|
| **Actor** | Administrador; Responsable de área en su área; Agente asignado. `ADMINISTRATIVE` no completa. |
| **Objetivo** | Registrar la solución y cerrar el ticket. |
| **Precondiciones** | Ticket `IN_PROGRESS`, no eliminado; comprobante PDF válido; solución obligatoria (D-34). |
| **Permiso** | `P-TICKET_COMPLETE` y `P-RECEIPT_UPLOAD`. |
| **Alcance** | `S-GLOBAL`, `S-AREA` o `S-OWN_ASSIGNED`. |

**Flujo principal:**

1. El actor ingresa la solución/materiales y adjunta el comprobante.
2. El backend valida estado, solución, tipo PDF y tamaño.
3. Guarda el archivo y sus metadatos.
4. Crea un `TicketClosure` de tipo `COMPLETED` con `solved_at`, `solution` y actor; cambia a `COMPLETED`.
5. Crea historial y auditoría.

**Flujos alternativos:**

- A1. Sin solución: `422` (D-34).
- A2. Sin comprobante: `422`.
- A3. Tipo no permitido: `415`.
- A4. Archivo demasiado grande: `413`.
- A5. Estado distinto de `IN_PROGRESS`: `409`.
- A6. Falla de almacenamiento: no se completa; se informa error.

**Errores y restricciones:** el comprobante es obligatorio (D-11); no se acepta cualquier archivo; no se reemplaza después (D-25).

**Reglas:** RN-070 a RN-074.

---

### UC-TKT-09 — Cancelar ticket

| Campo | Valor |
|---|---|
| **Actor** | Administrador; Responsable de área; Agente asignado. |
| **Objetivo** | Cerrar un ticket que no se ejecutará. |
| **Precondiciones** | Ticket `PENDING` o `IN_PROGRESS`, no eliminado; motivo obligatorio (D-34). |
| **Permiso** | `P-TICKET_CANCEL`. |
| **Alcance** | `S-GLOBAL`, `S-AREA` o `S-OWN_ASSIGNED`. |

**Flujo principal:**

1. El actor ingresa el motivo.
2. El backend valida estado, motivo, alcance y versión.
3. Crea un `TicketClosure` de tipo `CANCELLED` con `cancelled_at`, `cancellation_reason` y actor; cambia a `CANCELLED`.
4. Crea historial y auditoría.

**Flujos alternativos:**

- A1. Ticket completado/cancelado/eliminado: `409`.
- A2. Motivo vacío: `422` (D-34).
- A3. Agente intenta cancelar un ticket ajeno: `403`.

**Errores y restricciones:** el Agente puede cancelar el propio (D-12).

**Reglas:** RN-035, RN-036, RN-082.

---

### UC-TKT-10 — Eliminar ticket

| Campo | Valor |
|---|---|
| **Actor** | Administrador o Responsable de área. |
| **Objetivo** | Dar de baja lógica un ticket. |
| **Precondiciones** | Ticket no eliminado; motivo obligatorio (D-34). |
| **Permiso** | `P-TICKET_DELETE`. |
| **Alcance** | `S-GLOBAL` o `S-AREA`. |

**Flujo principal:**

1. El actor ingresa el motivo.
2. El backend valida estado, motivo, alcance y versión.
3. Marca el ticket como eliminado sin cambiar su estado.
4. Crea historial y auditoría.
5. El ticket pasa al listado de eliminados si el usuario tiene alcance.

**Flujos alternativos:**

- A1. Ticket ya eliminado: `409`.
- A2. Motivo vacío: `422` (D-34).
- A3. Administrativo o Agente intenta eliminar: `403`.

**Errores y restricciones:** no se borran filas ni archivos; la restauración es posible (D-01).

**Reglas:** RN-035, RN-068, RN-069.

---

### UC-TKT-11 — Restaurar ticket

| Campo | Valor |
|---|---|
| **Actor** | Administrador; Responsable de área en su área. |
| **Objetivo** | Reincorporar un ticket eliminado. |
| **Precondiciones** | Ticket eliminado. Si el responsable actual está inactivo, ya no pertenece al área o dejó de ser elegible, la operación exige reasignar a un usuario activo y elegible del área o dejar el ticket sin responsable. |
| **Permiso** | `P-TICKET_RESTORE`. |
| **Alcance** | `S-GLOBAL` o `S-AREA`. |

**Flujo principal:**

1. El actor solicita restaurar.
2. El backend valida que esté eliminado y que la asignación sea válida o haya sido resuelta por reasignación/desasignación.
3. Limpia la marca de eliminación y registra fecha/actor.
4. Conserva el estado de ciclo de vida, incluido un estado cerrado.
5. Crea historial y auditoría.

**Flujos alternativos:**

- A1. Ticket no eliminado: `409`.
- A2. Área inactiva: se restaura y se marca para revisión (D-18).
- A3. Conflicto de versión: `409`.

**Errores y restricciones:** no se restaura a un estado distinto del previo (D-01).

**Reglas:** RN-069.

---

### UC-TKT-12 — Reabrir ticket

| Campo | Valor |
|---|---|
| **Actor** | Administrador; Administrativo en su área; Responsable de área en su área. |
| **Objetivo** | Volver a operar un ticket cerrado que recibió quejas. |
| **Precondiciones** | Ticket `COMPLETED` o `CANCELLED`, no eliminado; motivo obligatorio (D-30). Si el responsable actual está inactivo, ya no pertenece al área o dejó de ser elegible, la operación exige reasignar a un usuario activo y elegible del área o dejar el ticket sin responsable. |
| **Permiso** | `P-TICKET_REOPEN`. |
| **Alcance** | `S-GLOBAL` o `S-AREA`. |

**Flujo principal:**

1. El actor solicita reabrir e ingresa el motivo.
2. El backend valida estado, motivo, alcance, versión y validez de la asignación actual o su resolución.
3. Cambia a `PENDING` o `IN_PROGRESS`, según se elija por ticket (D-30).
4. Registra `REOPENED` con estado anterior/nuevo, actor y motivo.
5. Conserva el `TicketClosure` anterior y el comprobante; el responsable debe ser el mismo que al cierre.

**Flujos alternativos:**

- A1. Ticket no cerrado: `409`.
- A2. Ticket eliminado: `409`; primero restaurar.
- A3. Sin motivo: `422` (D-30).
- A4. Responsable distinto al del cierre: `409`.
- A5. Conflicto de versión: `409`.

**Errores y restricciones:** D-13/D-30 aprueban la reapertura; los cierres históricos se conservan.

**Reglas:** RN-064, RN-065.

---

### UC-TKT-13 — Descargar comprobante

| Campo | Valor |
|---|---|
| **Actor** | Cualquier rol con alcance sobre el ticket. |
| **Objetivo** | Obtener el archivo comprobante. |
| **Precondiciones** | El cierre (`TicketClosure`) tiene comprobante; el usuario tiene permiso y alcance. |
| **Permiso** | `P-RECEIPT_DOWNLOAD`. |
| **Alcance** | Según rol. |

**Flujo principal:**

1. El usuario solicita la descarga.
2. El backend valida permiso y alcance sobre el ticket.
3. Recupera el comprobante del `TicketClosure` (último o histórico según el parámetro).
4. Lo devuelve con `Content-Disposition: attachment` y el nombre original saneado.
5. No se audita la descarga (D-22).

**Flujos alternativos:**

- A1. Sin comprobante: `404`.
- A2. Sin permiso/alcance: `403` o `404`.
- A3. Archivo faltante: `404` con código específico; se registra el incidente operativo.

**Errores y restricciones:** no hay URL pública directa (Q-019).

**Reglas:** RN-071 a RN-073.

---

### UC-TKT-14 — Consultar historial de ticket

| Campo | Valor |
|---|---|
| **Actor** | Cualquier rol con alcance sobre el ticket. |
| **Objetivo** | Ver la línea de tiempo del ticket. |
| **Precondiciones** | El ticket existe y está dentro del alcance. |
| **Permiso** | `P-TICKET_VIEW_HISTORY`. |
| **Alcance** | Según rol. |

**Flujo principal:**

1. El usuario abre el historial.
2. El backend devuelve las entradas ordenadas por fecha.
3. Cada entrada muestra tipo, actor, fecha, estado anterior/nuevo y motivo cuando corresponde.

**Flujos alternativos:**

- A1. Sin entradas adicionales: se muestra al menos `CREATED`.
- A2. Ticket fuera de alcance: `404`.

**Errores y restricciones:** el historial es de sólo lectura.

**Reglas:** RN-083, RN-088.

---

## D. Reportes y auditoría de área

### UC-REP-01 — Consultar estadísticas

| Campo | Valor |
|---|---|
| **Actor** | Administrador (global); Administrativo y Responsable (área). |
| **Objetivo** | Analizar la operación en un período. |
| **Precondiciones** | Sesión activa. |
| **Permiso** | `P-STATISTICS_VIEW`; `P-STATISTICS_VIEW_GLOBAL` para global. |
| **Alcance** | `S-AREA` o `S-GLOBAL`. |

**Flujo principal:**

1. El usuario selecciona período (hoy, rango, año, total) y filtros.
2. El backend valida rango y alcance.
3. Calcula KPIs: ingresados, completados, cancelados, eliminados, por estado, por área y por tipo (`INCIDENT`/`TASK`).
4. Devuelve resultados.
5. El usuario puede exportarlos si tiene permiso.

**Flujos alternativos:**

- A1. Rango inválido: `422`.
- A2. Sin datos: ceros.
- A3. Intento de ver otra área: denegado.
- A4. Agente: sin acceso.

**Errores y restricciones:** criterios en `reporting-and-files.md` y D-08; desglose por tipo aprobado por D-32; no se agrega KPI de reaperturas.

**Reglas:** RN-090 a RN-100.

---

### UC-REP-02 — Exportar listado o reporte

| Campo | Valor |
|---|---|
| **Actor** | Administrador, Administrativo o Responsable. |
| **Objetivo** | Obtener un archivo con los datos filtrados. |
| **Precondiciones** | El usuario tiene permiso de lectura y exportación. |
| **Permiso** | `P-TICKET_EXPORT` o `P-STATISTICS_EXPORT`. |
| **Alcance** | Según rol. |

**Flujo principal:**

1. El usuario aplica filtros y solicita exportar.
2. El backend valida permiso, alcance y tamaño.
3. Genera el archivo: CSV/XLSX para listados, PDF para reportes.
4. Lo devuelve como descarga síncrona.
5. No se audita la exportación (D-22).

**Flujos alternativos:**

- A1. Formato no soportado: `415`.
- A2. Volumen excesivo: `413` con aviso; no se genera asíncrono en la primera versión (D-24).
- A3. Sin permiso: `403`.

**Errores y restricciones:** límite configurable y aviso (D-24).

**Reglas:** RN-099.

---

### UC-REP-03 — Consultar auditoría de área

| Campo | Valor |
|---|---|
| **Actor** | Administrador (global); Administrativo y Responsable (área); Agente para sus tickets asignados. |
| **Objetivo** | Revisar los cambios de las entidades del área o de los tickets propios. |
| **Precondiciones** | Sesión activa. |
| **Permiso** | `P-AUDIT_VIEW`. |
| **Alcance** | `S-AREA`, `S-GLOBAL` o `S-OWN_ASSIGNED`. |

**Flujo principal:**

1. El usuario filtra por fecha, actor, entidad o acción.
2. El backend aplica el alcance del área.
3. Devuelve los eventos paginados.
4. El usuario consulta el detalle de un evento.

**Flujos alternativos:**

- A1. Sin resultados: lista vacía.
- A2. Evento de otra área: oculto/denegado.
- A3. Exportación: sujeta a permiso; no se audita.

**Errores y restricciones:** no se modifican eventos; no incluye lecturas/descargas (D-22).

**Reglas:** RN-084 a RN-087.

---

## Matriz resumida de casos de uso por actor

| Caso de uso | `SUPERUSER` | `ADMINISTRATIVE` | `AREA_MANAGER` | `AGENT` |
|---|:---:|:---:|:---:|:---:|
| UC-AUT-01 a UC-AUT-06 | ✅ | ✅ | ✅ | ✅ |
| UC-ADM-01 a UC-ADM-06 | ✅ | — | — | — |
| UC-ADM-07 | ✅ lectura | ✅ lectura | ✅ lectura | ✅ lectura |
| UC-ADM-08, UC-ADM-09 | ✅ | — | — | — |
| UC-ADM-10 | ✅ global | ✅ área | ✅ área | — |
| UC-ADM-11 | ✅ | — | — | — |
| UC-ADM-12 | ✅ | — | — | — |
| UC-TKT-01 | ✅ | ✅ | ✅ | — |
| UC-TKT-02, UC-TKT-03 | ✅ | ✅ área | ✅ área | ✅ cola |
| UC-TKT-04 | ✅ | ✅ propias | ✅ área | — |
| UC-TKT-05 | ✅ | ✅ asignar | ✅ asignar/reasignar | — |
| UC-TKT-06 | ✅ si elegible | — | — | ✅ |
| UC-TKT-07 | ✅ | ✅ área | ✅ área | ✅ propias |
| UC-TKT-08 | ✅ | — | ✅ área | ✅ propias |
| UC-TKT-09 | ✅ | — | ✅ área | ✅ propias |
| UC-TKT-10 | ✅ | — | ✅ área | — |
| UC-TKT-11 | ✅ | — | ✅ área | — |
| UC-TKT-12 | ✅ | ✅ área | ✅ área | — |
| UC-TKT-13, UC-TKT-14 | ✅ | ✅ área | ✅ área | ✅ propias |
| UC-REP-01 | ✅ global | ✅ área | ✅ área | — |
| UC-REP-02 | ✅ global | ✅ área | ✅ área | — |
| UC-REP-03 | ✅ global | ✅ área | ✅ área | ✅ propios |

> La matriz es una vista resumida; la autorización definitiva se rige por [`roles-and-permissions.md`](./roles-and-permissions.md).
