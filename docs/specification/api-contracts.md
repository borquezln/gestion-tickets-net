# Contratos funcionales de API

> **Fase 1 — Revisión 4.** Contratos resource-oriented, sin código ni DTOs implementados.
> La entidad central es `Ticket`; el modelo y los campos están en inglés. La API usa `snake_case` para los campos de dominio.
> Las decisiones D-01 a D-39 están incorporadas; no quedan operaciones pendientes.

## Índice

1. [Principios](#1-principios)
2. [Convenciones comunes](#2-convenciones-comunes)
3. [Mapa de recursos](#3-mapa-de-recursos)
4. [Modelo común de errores](#4-modelo-común-de-errores)
5. [Autenticación y sesión](#5-autenticación-y-sesión)
6. [Perfil propio](#6-perfil-propio)
7. [Usuarios y membresías](#7-usuarios-y-membresías)
8. [Roles y permisos](#8-roles-y-permisos)
9. [Direcciones](#9-direcciones)
10. [Áreas](#10-áreas)
11. [Tickets](#11-tickets)
12. [Estadísticas](#12-estadísticas)
13. [Auditoría](#13-auditoría)
14. [Configuración](#14-configuración)
15. [Mapeo casos de uso → operaciones](#15-mapeo-casos-de-uso--operaciones)
16. [Resumen de autorización](#16-resumen-de-autorización)
17. [Decisiones incorporadas](#17-decisiones-incorporadas)

---

## 1. Principios

1. **Recursos, no acciones del legado.** No se copian rutas como `listarTareasAdmin`, `c_agregarTarea` o `verEstadisticas`.
2. **Autorización por operación.** Cada operación declara permiso y alcance; el backend deniega por defecto.
3. **Contratos estables.** Prefijo `/api/v1`.
4. **Errores consistentes.** Formato único con código de dominio.
5. **Sin lógica en el cliente.** Las validaciones de negocio se aplican en el servidor.
6. **Concurrencia explícita.** ETag/`If-Match` en escrituras; `409` en conflicto.
7. **Archivos protegidos.** El comprobante se descarga por endpoint autenticado, nunca por URL estática.
8. **Modelo en inglés.** Recursos, campos, enums y códigos usan inglés.

## 2. Convenciones comunes

### 2.1 Formato

| Aspecto | Convención |
|---|---|
| Base | `/api/v1` |
| Formato | JSON (`application/json`) salvo descargas y cargas multipart. |
| Fechas | ISO 8601 con offset; el backend interpreta en la zona configurada (D-14). |
| Identificadores | UUID como cadenas opacas. |
| Número de ticket | `ticket_number` visible, separado de `id`. |
| Paginación | `page` (1-based) y `page_size`; respuesta con `items`, `page`, `page_size`, `total`, `total_pages`. |
| Orden | `sort=field` o `sort=-field` para descendente. |
| Filtros | Parámetros de query explícitos; no se usan nombres del legado. |
| Concurrencia | `version`/ETag y `If-Match` en escrituras sensibles (D-27). |
| Idioma | Mensajes en español; códigos estables en inglés. |

### 2.2 Paginación

| Campo | Tipo | Descripción |
|---|---|---|
| `items` | array | Elementos de la página. |
| `page` | int | Página actual. |
| `page_size` | int | Tamaño solicitado. |
| `total` | int | Total de elementos que cumplen el filtro. |
| `total_pages` | int | Total de páginas. |

Límites de primera versión: `page_size` por defecto 20, máximo 100. Ajuste de Fase 2.

### 2.3 Fechas y zonas

- Las fechas se transportan en ISO 8601.
- El backend almacena en UTC y convierte a la zona configurada.
- Los filtros por día usan la zona configurada, no UTC.
- Default: `America/Argentina/Buenos_Aires`; configurable por el Administrador (D-14).

### 2.4 Idempotencia y concurrencia

- Las transiciones de estado no son idempotentes: repetir “start” sobre un ticket ya iniciado devuelve `409`.
- Las altas pueden aceptar `Idempotency-Key` opcional (D-27).
- Las escrituras incluyen `If-Match` con ETag; una versión desactualizada devuelve `409`.

### 2.5 Seguridad de transporte

- HTTPS obligatorio.
- Cookie de sesión `HttpOnly`, `Secure` y `SameSite` (D-07).
- No se usa token en `localStorage`.
- CORS restringido al origen del frontend.
- CSRF según el mecanismo de cookie/sesión que se defina en Fase 2.

## 3. Mapa de recursos

| Recurso | Ruta base | Operaciones principales |
|---|---|---|
| Sesión | `/auth` | Login, logout, sesión actual. |
| Perfil propio | `/me` | Consultar, editar, cambiar contraseña. |
| Usuarios | `/users` | Listar, crear, consultar, editar, baja, alta, membresías, contraseña, asignables. |
| Roles | `/roles` | Listar y consultar; sólo lectura. |
| Permisos | `/permissions` | Listar catálogo; sólo lectura. |
| Direcciones | `/directions` | Listar, crear, consultar, editar, baja, alta. |
| Áreas | `/areas` | Listar, crear, consultar, editar, baja, alta. |
| Tickets | `/tickets` | Listar, crear, consultar, editar, asignar, tomar, iniciar, completar, cancelar, eliminar, restaurar, reabrir, comprobante, historial. |
| Estadísticas | `/statistics` | Resumen, series, exportación. |
| Auditoría | `/audit-events` | Listar y consultar. |
| Configuración | `/settings` | Consultar y actualizar zona horaria. |

## 4. Modelo común de errores

### 4.1 Estructura

| Campo | Tipo | Descripción |
|---|---|---|
| `type` | string | URI o identificador del tipo de error. |
| `title` | string | Descripción breve. |
| `status` | int | Código HTTP. |
| `detail` | string | Detalle legible. |
| `code` | string | Código estable de dominio. |
| `errors` | object? | Errores por campo (`field: [messages]`). |
| `correlation_id` | string | Identificador para soporte/auditoría. |
| `timestamp` | string | Fecha ISO 8601. |

### 4.2 Códigos HTTP

| Código | Uso |
|---|---|
| `200` | Consulta o actualización exitosa. |
| `201` | Creación exitosa; incluye `Location`. |
| `204` | Operación sin contenido. |
| `400` | Petición mal formada. |
| `401` | No autenticado o sesión expirada. |
| `403` | Autenticado pero sin permiso. |
| `404` | Recurso inexistente o fuera de alcance. |
| `409` | Conflicto de estado, duplicado, concurrencia o regla de negocio. |
| `413` | Archivo o payload demasiado grande. |
| `415` | Tipo de contenido/archivo no soportado. |
| `422` | Validación de datos. |
| `429` | Demasiadas solicitudes. |
| `500` | Error interno. |

### 4.3 Códigos de dominio

| Código | Significado |
|---|---|
| `AUTH_INVALID_CREDENTIALS` | Credenciales inválidas o usuario inactivo. |
| `AUTH_SESSION_EXPIRED` | Sesión expirada. |
| `AUTH_FORBIDDEN` | Sin permiso. |
| `USER_DUPLICATE_LEGAJO` | Legajo duplicado. |
| `USER_DUPLICATE_USERNAME` | Usuario duplicado. |
| `USER_DUPLICATE_EMAIL` | Correo duplicado. |
| `USER_EMAIL_NOT_INTERNAL` | Correo fuera del dominio interno. |
| `USER_INACTIVE` | Usuario inactivo. |
| `USER_LAST_ADMIN` | Último Administrador activo. |
| `USER_HAS_ACTIVE_ASSIGNMENTS` | Usuario con tickets activos asignados. |
| `USER_MEMBERSHIP_REQUIRED` | Usuario sin membresías. |
| `USER_MEMBERSHIP_DUPLICATE` | Membresía duplicada para el área. |
| `ROLE_NOT_FOUND` | Rol inexistente. |
| `ROLE_MANAGEMENT_DISABLED` | Gestión dinámica de roles no disponible. |
| `DIRECTION_CODE_DUPLICATE` | Código de dirección duplicado entre activos. |
| `DIRECTION_IN_USE` | Dirección con áreas activas. |
| `AREA_IN_USE` | Área con usuarios/tickets activos. |
| `AREA_INACTIVE` | Área inactiva. |
| `TICKET_INVALID_STATE` | Transición inválida para el estado actual. |
| `TICKET_NOT_ASSIGNED_TO_ACTOR` | El Agente no es el responsable. |
| `TICKET_RESPONSIBLE_INACTIVE` | Responsable inactivo. |
| `TICKET_RESPONSIBLE_OUT_OF_AREA` | Responsable fuera del área. |
| `TICKET_ALREADY_DELETED` | Ticket ya eliminado. |
| `TICKET_ASSIGNEE_INVALID` | El responsable actual no es válido y debe reasignarse o desasignarse. |
| `TICKET_NOT_DELETED` | Ticket no eliminado. |
| `TICKET_RECEIPT_REQUIRED` | Comprobante obligatorio. |
| `TICKET_RECEIPT_INVALID_TYPE` | Tipo de comprobante no permitido. |
| `TICKET_RECEIPT_TOO_LARGE` | Comprobante demasiado grande. |
| `TICKET_VERSION_CONFLICT` | Versión desactualizada. |
| `TICKET_NOT_EDITABLE` | Estado no permite edición. |
| `TICKET_TYPE_INVALID` | Tipo distinto de `INCIDENT`/`TASK`. |
| `TICKET_FUTURE_DATE_NOT_ALLOWED` | `INCIDENT` con `problem_date` futura. |
| `TICKET_REOPEN_NOT_ALLOWED` | Ticket no cerrado o ya eliminado. |
| `SETTING_INVALID_TIMEZONE` | Zona horaria inválida. |
| `VALIDATION_ERROR` | Error genérico de validación. |
| `RANGE_INVALID` | Rango de fechas inválido. |
| `EXPORT_TOO_LARGE` | Exportación demasiado grande. |

## 5. Autenticación y sesión

### 5.1 `POST /api/v1/auth/login`

| Aspecto | Definición |
|---|---|
| Propósito | Iniciar sesión. |
| Autorización | Pública. |
| Entrada | `identifier` (usuario o correo, D-38), `password`. |
| Salida | `user` (id, legajo, nombre, correo, `last_login_at`), `memberships` (área + rol), `permissions`, `expires_at`. |
| Validaciones | Campos obligatorios; credenciales válidas; usuario `ACTIVE`; al menos una membresía activa. |
| Errores | `401` (`AUTH_INVALID_CREDENTIALS`), `422`, `429`. |
| Reglas | RN-011, RN-012, RN-015, RN-016, RN-019. |

### 5.2 `POST /api/v1/auth/logout`

| Aspecto | Definición |
|---|---|
| Propósito | Cerrar sesión. |
| Autorización | Autenticado. |
| Entrada | Ninguna. |
| Salida | `204`. |
| Errores | `401` si la sesión ya no existe. |

### 5.3 `GET /api/v1/auth/session`

| Aspecto | Definición |
|---|---|
| Propósito | Consultar la sesión actual y los permisos efectivos. |
| Autorización | Autenticado. |
| Salida | Usuario, membresías, permisos, `expires_at`. |
| Errores | `401`. |
| Reglas | RN-011, RN-012. |

> No se define `POST /auth/refresh`: la sesión de servidor con cookie deslizante no lo requiere (D-07). Si en Fase 2 se adopta otro mecanismo, se documentará.

## 6. Perfil propio

### 6.1 `GET /api/v1/me`

| Aspecto | Definición |
|---|---|
| Autorización | `P-PROFILE_VIEW`, `S-SELF`. |
| Salida | `legajo`, `first_name`, `last_name`, `username`, `email`, `memberships`, `permissions`, `last_login_at` (D-38). |
| Errores | `401`. |

### 6.2 `PATCH /api/v1/me`

| Aspecto | Definición |
|---|---|
| Autorización | `P-PROFILE_EDIT`, `S-SELF`. |
| Entrada | `first_name`, `last_name`, `email` (D-33). |
| Validaciones | Formato, unicidad, correo interno, campos no vacíos. |
| Salida | Perfil actualizado. |
| Errores | `409`, `422`, `401`. |
| Restricciones | No permite cambiar rol, áreas, legajo ni `username` (D-33). |

### 6.3 `PUT /api/v1/me/password`

| Aspecto | Definición |
|---|---|
| Autorización | `P-PROFILE_CHANGE_PASSWORD`, `S-SELF`. |
| Entrada | `current_password`, `new_password`, `confirm_password`. |
| Validaciones | Contraseña actual (control de seguridad), coincidencia y política. |
| Salida | `204`. |
| Errores | `401`, `422`. |

## 7. Usuarios y membresías

### 7.1 `GET /api/v1/users`

| Aspecto | Definición |
|---|---|
| Autorización | `P-USER_VIEW`, `S-GLOBAL`. |
| Query | `status` (default `ACTIVE`), `include_inactive`, `area_id`, `role_code`, `search`, `page`, `page_size`, `sort`. |
| Salida | Listado paginado de usuarios (sin hash), con membresías y `last_login_at`. |
| Errores | `403`. |
| Nota | Los inactivos se excluyen de los listados operativos; `include_inactive` sólo aplica a la gestión del `SUPERUSER` (D-33). |

### 7.2 `GET /api/v1/users/{id}`

| Aspecto | Definición |
|---|---|
| Autorización | `P-USER_VIEW`, `S-GLOBAL`. |
| Salida | Detalle de usuario, membresías, estado. |
| Errores | `404`. |

### 7.3 `POST /api/v1/users`

| Aspecto | Definición |
|---|---|
| Autorización | `P-USER_CREATE`, `S-GLOBAL`. |
| Entrada | `legajo`, `first_name`, `last_name`, `username`, `email`, `password`, `memberships`: `[{area_id, role_id}]`. |
| Validaciones | Unicidad de legajo/usuario/correo; correo interno; áreas/roles activos; al menos una membresía; una por área. |
| Salida | `201` + usuario creado. |
| Errores | `409` duplicados, `422`, `403`. |
| Reglas | RN-016, RN-017, RN-018, RN-030, RN-037. |

### 7.4 `PATCH /api/v1/users/{id}`

| Aspecto | Definición |
|---|---|
| Autorización | `P-USER_EDIT`, `S-GLOBAL`. |
| Entrada | `first_name`, `last_name`, `email`, `username`, `memberships` (parcial; sólo `SUPERUSER`). |
| Validaciones | Unicidad, correo interno, referencias, compatibilidad con asignaciones; no dejar sin `SUPERUSER` activo (D-36). |
| Salida | Usuario actualizado. |
| Errores | `409`, `422`, `404`. |
| Restricciones | No cambia legajo. El cambio de rol/área invalida sesiones. |

### 7.5 `POST /api/v1/users/{id}/deactivation`

| Aspecto | Definición |
|---|---|
| Autorización | `P-USER_DEACTIVATE`, `S-GLOBAL`. |
| Entrada | `reason`. |
| Validaciones | No último `SUPERUSER` activo (D-36); no auto-baja; sin tickets activos asignados o con reasignación explícita. |
| Salida | `204` o usuario actualizado. |
| Errores | `409` (`USER_LAST_ADMIN`, `USER_HAS_ACTIVE_ASSIGNMENTS`), `422`. |
| Efectos | Invalida sesiones; audita. |

### 7.6 `POST /api/v1/users/{id}/reactivation`

| Aspecto | Definición |
|---|---|
| Autorización | `P-USER_REACTIVATE`, `S-GLOBAL`. |
| Entrada | `memberships`, `new_password`, `confirm_password`. |
| Validaciones | Áreas/roles activos; nueva contraseña válida. |
| Salida | Usuario actualizado. |
| Errores | `409` si ya está activo, `422`. |
| Efectos | Exige nuevo blanqueo (D-07); audita. |

### 7.7 `PUT /api/v1/users/{id}/memberships/{membership_id}/role`

| Aspecto | Definición |
|---|---|
| Autorización | `P-USER_CHANGE_ROLE`, `S-GLOBAL`. |
| Entrada | `role_id`, `version`. |
| Validaciones | Rol existente; no dejar sin `SUPERUSER` activo (D-36); compatibilidad con asignaciones. |
| Salida | Usuario actualizado. |
| Errores | `409`, `422`. |
| Efectos | Invalida sesiones; audita. |

### 7.8 `PUT /api/v1/users/{id}/password`

| Aspecto | Definición |
|---|---|
| Autorización | `P-USER_RESET_PASSWORD`, `S-GLOBAL`. |
| Entrada | `new_password`, `confirm_password`. |
| Validaciones | Coincidencia y política. |
| Salida | `204`. |
| Errores | `422`, `404`. |
| Efectos | Invalida sesiones; audita. |

### 7.9 `GET /api/v1/users/assignable`

| Aspecto | Definición |
|---|---|
| Autorización | `P-USER_VIEW_ASSIGNABLE`; `S-AREA` o `S-GLOBAL`. |
| Query | `area_id`, `role_code` (`AGENT`/`AREA_MANAGER`), `search`. |
| Salida | Usuarios activos elegibles para asignación. |
| Errores | `403`. |

## 8. Roles y permisos

> Catálogo fijo (D-17). Sólo lectura; no hay CRUD de roles.

### 8.1 `GET /api/v1/roles`

| Aspecto | Definición |
|---|---|
| Autorización | `P-ROLE_VIEW`. |
| Salida | Roles con permisos. |

### 8.2 `GET /api/v1/permissions`

| Aspecto | Definición |
|---|---|
| Autorización | `P-ROLE_VIEW`. |
| Salida | Catálogo de permisos. |

> `P-ROLE_MANAGE` está reservado; no se exponen `POST /roles` ni `PATCH /roles/{id}`.

## 9. Direcciones

### 9.1 `GET /api/v1/directions`

| Aspecto | Definición |
|---|---|
| Autorización | `P-DIRECTION_VIEW`; los no administradores ven la dirección derivada de sus áreas, no el catálogo global (D-29). |
| Query | `include_inactive`, `search`, `page`, `page_size`. |
| Salida | Direcciones paginadas. |

### 9.2 `GET /api/v1/directions/{id}`

| Aspecto | Definición |
|---|---|
| Autorización | `P-DIRECTION_VIEW`. |
| Salida | Detalle de dirección. |

### 9.3 `POST /api/v1/directions`

| Aspecto | Definición |
|---|---|
| Autorización | `P-DIRECTION_MANAGE`, `S-GLOBAL`. |
| Entrada | `code`, `name`, `description?`. |
| Validaciones | Código único entre activos; nombre obligatorio. |
| Salida | `201` + dirección. |
| Errores | `409` (`DIRECTION_CODE_DUPLICATE`), `422`. |

### 9.4 `PATCH /api/v1/directions/{id}`

| Aspecto | Definición |
|---|---|
| Autorización | `P-DIRECTION_MANAGE`, `S-GLOBAL`. |
| Entrada | `code`, `name`, `description` (parcial). |
| Validaciones | Código único entre activos; no vacío. |
| Salida | Dirección actualizada. |
| Errores | `409`, `422`. |

### 9.5 `POST /api/v1/directions/{id}/deactivation`

| Aspecto | Definición |
|---|---|
| Autorización | `P-DIRECTION_MANAGE`, `S-GLOBAL`. |
| Entrada | `reason?`. |
| Validaciones | Sin áreas activas dependientes (D-18); referencias históricas se conservan. |
| Salida | `204` o dirección actualizada. |
| Errores | `409` (`DIRECTION_IN_USE`). |

### 9.6 `POST /api/v1/directions/{id}/reactivation`

| Aspecto | Definición |
|---|---|
| Autorización | `P-DIRECTION_MANAGE`, `S-GLOBAL`. |
| Salida | Dirección reactivada. |
| Errores | `409` si ya está activa. |

## 10. Áreas

### 10.1 `GET /api/v1/areas`

| Aspecto | Definición |
|---|---|
| Autorización | `P-AREA_VIEW`; no administradores ven sólo sus áreas. |
| Query | `include_inactive`, `direction_id`, `search`, `page`, `page_size`. |
| Salida | Áreas paginadas. |

### 10.2 `GET /api/v1/areas/{id}`

| Aspecto | Definición |
|---|---|
| Autorización | `P-AREA_VIEW`. |
| Salida | Detalle de área con dirección padre. |

### 10.3 `POST /api/v1/areas`

| Aspecto | Definición |
|---|---|
| Autorización | `P-AREA_MANAGE`, `S-GLOBAL`. |
| Entrada | `name`, `description?`, `direction_id`. |
| Validaciones | Dirección activa; nombre obligatorio. |
| Salida | `201` + área. |
| Errores | `422`, `403`. |

### 10.4 `PATCH /api/v1/areas/{id}`

| Aspecto | Definición |
|---|---|
| Autorización | `P-AREA_MANAGE`, `S-GLOBAL`. |
| Entrada | `name`, `description`, `direction_id` (parcial). |
| Validaciones | Dirección activa; referencias. |
| Salida | Área actualizada. |
| Errores | `422`, `409`. |

### 10.5 `POST /api/v1/areas/{id}/deactivation`

| Aspecto | Definición |
|---|---|
| Autorización | `P-AREA_MANAGE`, `S-GLOBAL`. |
| Entrada | `reason?`. |
| Validaciones | Sin usuarios/tickets activos o con reasignación explícita (D-18). |
| Salida | `204` o área actualizada. |
| Errores | `409` (`AREA_IN_USE`). |

### 10.6 `POST /api/v1/areas/{id}/reactivation`

| Aspecto | Definición |
|---|---|
| Autorización | `P-AREA_MANAGE`, `S-GLOBAL`. |
| Salida | Área reactivada. |
| Errores | `409` si ya está activa. |

## 11. Tickets

### 11.1 `GET /api/v1/tickets`

| Aspecto | Definición |
|---|---|
| Autorización | `P-TICKET_VIEW`; `P-TICKET_VIEW_DELETED` para `view=deleted`. |
| Alcance | `S-GLOBAL`, `S-AREA` o `S-AGENT_QUEUE`. |
| Query | `view` (`current`, `completed`, `cancelled`, `deleted`; default `current`), `type`, `status`, `area_id`, `assigned_user_id`, `created_by_user_id`, `date_from`, `date_to`, `date_field`, `search`, `page`, `page_size`, `sort`. |
| Salida | Listado paginado de tickets (resumen). |
| Validaciones | Rango válido; filtros conocidos; `date_field` en `problem_date`/`created_at`/`solved_at`/`cancelled_at`/`deleted_at`. |
| Errores | `422` (`RANGE_INVALID`), `403`. |
| Nota | `view=current` = `PENDING` + `IN_PROGRESS`; `completed`, `cancelled` y `deleted` son vistas separadas (D-35). `view=deleted` exige `P-TICKET_VIEW_DELETED`. |

**Campos de resumen:** `id`, `ticket_number`, `type`, `description` (resumida), `status`, `area`, `direction` (derivada de `Area.direction_id`, D-29), `affected_person_name`, `assigned_user`, `problem_date`, `created_at`, `is_deleted`, `version`.

### 11.2 `POST /api/v1/tickets`

| Aspecto | Definición |
|---|---|
| Autorización | `P-TICKET_CREATE`; `S-GLOBAL` o `S-AREA`. |
| Entrada | `type` (`INCIDENT`/`TASK`), `description`, `area_id`, `problem_date` (obligatorios), `electronic_note_id?`, `affected_person_name?`, `affected_phone?`. |
| Validaciones | Tipo válido; descripción y área obligatorias; referencias activas; Nota Electrónica válida; `problem_date` futura sólo `TASK`. |
| Salida | `201` + ticket creado, con `ticket_number`. |
| Errores | `422`, `403`, `TICKET_FUTURE_DATE_NOT_ALLOWED`. |
| Efectos | Estado `PENDING` (D-34), `created_at`, `created_by_user_id`, historial y auditoría. La dirección se deriva del área (D-29). |
| Reglas | RN-003, RN-060, RN-061, RN-062. |

### 11.3 `GET /api/v1/tickets/{id}`

| Aspecto | Definición |
|---|---|
| Autorización | `P-TICKET_VIEW`; `P-TICKET_VIEW_DELETED` si está eliminado. |
| Alcance | Según rol. |
| Salida | Detalle completo: datos, tipo, estado, dirección derivada, asignación, eliminación, reapertura, último `TicketClosure`, cierres históricos (resumen), comprobante del cierre (metadatos), historial resumido y `allowed_actions`. |
| Errores | `404` si no existe o está fuera de alcance. |

### 11.4 `PATCH /api/v1/tickets/{id}`

| Aspecto | Definición |
|---|---|
| Autorización | `P-TICKET_EDIT`; `S-GLOBAL`, `S-OWN_CREATED` o `S-AREA`. |
| Entrada | `type`, `description`, `electronic_note_id`, `affected_person_name`, `affected_phone`, `area_id`, `problem_date`, `version` (parcial). |
| Validaciones | Estado `PENDING`/`IN_PROGRESS`; referencias activas; compatibilidad con responsable; `problem_date`; versión. |
| Salida | Ticket actualizado. |
| Errores | `409` (`TICKET_NOT_EDITABLE`, `TICKET_VERSION_CONFLICT`), `422`, `403`. |
| Restricciones | No cambia estado, creador, `created_at`, `ticket_number`, cierre, comprobante, cancelación ni eliminación (D-34). |

### 11.5 `PUT /api/v1/tickets/{id}/assignment`

| Aspecto | Definición |
|---|---|
| Autorización | `P-TICKET_ASSIGN`; `P-TICKET_REASSIGN` si ya tenía responsable. |
| Alcance | `S-GLOBAL` o `S-AREA`. |
| Entrada | `assigned_user_id` (nullable), `version`. |
| Validaciones | Ticket `PENDING`/`IN_PROGRESS`, no eliminado; usuario activo, elegible y del área. |
| Salida | Ticket actualizado. |
| Errores | `409`, `422`, `403`. |
| Reglas | `ADMINISTRATIVE` asigna; `AREA_MANAGER` asigna/reasigna; `SUPERUSER` ambos. |

### 11.6 `POST /api/v1/tickets/{id}/take`

| Aspecto | Definición |
|---|---|
| Autorización | `P-TICKET_TAKE`; `S-AGENT_QUEUE` o `S-GLOBAL` para `SUPERUSER` elegible. |
| Entrada | `version`. |
| Validaciones | Ticket sin asignar, `PENDING`/`IN_PROGRESS`, no eliminado; actor `AGENT` con membresía en el área, o `SUPERUSER` si además es elegible como `AGENT`/`AREA_MANAGER`. |
| Salida | Ticket actualizado. |
| Errores | `409` si ya está asignado, `403`, `TICKET_VERSION_CONFLICT`. |

### 11.7 `POST /api/v1/tickets/{id}/start`

| Aspecto | Definición |
|---|---|
| Autorización | `P-TICKET_START`; `S-GLOBAL`, `S-AREA` o `S-OWN_ASSIGNED`. |
| Entrada | `version`. |
| Validaciones | Estado `PENDING`; no eliminado; alcance; si es `AGENT`, asignado a él. |
| Salida | Ticket actualizado. |
| Errores | `409` (`TICKET_INVALID_STATE`, `TICKET_VERSION_CONFLICT`), `403`. |
| Nota | No exige responsable (D-05). `ADMINISTRATIVE` y `AREA_MANAGER` pueden iniciar en su área; `AGENT` sólo el propio (D-31). |

### 11.8 `POST /api/v1/tickets/{id}/completion`

| Aspecto | Definición |
|---|---|
| Autorización | `P-TICKET_COMPLETE` y `P-RECEIPT_UPLOAD`; `S-GLOBAL`, `S-AREA` o `S-OWN_ASSIGNED`. |
| Entrada | `multipart/form-data`: `solution` (obligatoria), `receipt` (PDF), `version`. |
| Validaciones | Estado `IN_PROGRESS`; no eliminado; PDF válido y dentro del tamaño; solución obligatoria. |
| Salida | Ticket actualizado con `last_closure` y comprobante. |
| Errores | `409`, `422` (`TICKET_RECEIPT_REQUIRED`), `413`, `415`, `403`. |
| Efectos | Crea `TicketClosure` `COMPLETED` con `solved_at`, solución, actor y comprobante; estado `COMPLETED`; historial y auditoría. |
| Nota | `ADMINISTRATIVE` no completa; `AREA_MANAGER` y `AGENT` propio sí (D-31). |

### 11.9 `POST /api/v1/tickets/{id}/cancellation`

| Aspecto | Definición |
|---|---|
| Autorización | `P-TICKET_CANCEL`; `S-GLOBAL`, `S-AREA` o `S-OWN_ASSIGNED`. |
| Entrada | `reason` (obligatorio), `version`. |
| Validaciones | Estado `PENDING`/`IN_PROGRESS`; no eliminado; motivo obligatorio. |
| Salida | Ticket actualizado con `last_closure`. |
| Errores | `409`, `422`, `403`. |
| Efectos | Crea `TicketClosure` `CANCELLED` con `cancelled_at`, motivo y actor. |

### 11.10 `POST /api/v1/tickets/{id}/deletion`

| Aspecto | Definición |
|---|---|
| Autorización | `P-TICKET_DELETE`; `S-GLOBAL` o `S-AREA`. |
| Entrada | `reason` (obligatorio), `version`. |
| Validaciones | No eliminado; motivo obligatorio. |
| Salida | `204` o ticket actualizado. |
| Errores | `409` (`TICKET_ALREADY_DELETED`, `TICKET_VERSION_CONFLICT`), `422`. |

### 11.11 `POST /api/v1/tickets/{id}/restoration`

| Aspecto | Definición |
|---|---|
| Autorización | `P-TICKET_RESTORE`; `S-GLOBAL` o `S-AREA`. |
| Entrada | `assigned_user_id?` (nullable, para resolver una asignación inválida), `version`. |
| Validaciones | Ticket eliminado. Si el responsable actual es inválido, la operación exige `assigned_user_id` válido o nulo. |
| Semántica de `assigned_user_id` | Omitido: conservar el responsable si sigue siendo válido; si no, exigir resolución. `null`: dejar sin responsable. ID válido: reasignar al usuario indicado. ID inexistente o no elegible: rechazar la operación. La reasignación y la restauración son atómicas. |
| Salida | Ticket actualizado; conserva estado, cierres e historial. |
| Errores | `409` (`TICKET_NOT_DELETED`, `TICKET_VERSION_CONFLICT`, `TICKET_ASSIGNEE_INVALID`). |
| Nota | `SUPERUSER` y `AREA_MANAGER` en su área; `ADMINISTRATIVE` y `AGENT` no (D-31). Si el responsable es inválido, la reasignación o desasignación es una excepción explícita y no habilita la edición general del ticket. |

### 11.12 `POST /api/v1/tickets/{id}/reopen`

| Aspecto | Definición |
|---|---|
| Autorización | `P-TICKET_REOPEN`; `S-GLOBAL` o `S-AREA`. |
| Entrada | `reason` (obligatorio), `target_status` (`PENDING` o `IN_PROGRESS`), `assigned_user_id?` (nullable, para resolver una asignación inválida), `version`. |
| Validaciones | Ticket `COMPLETED`/`CANCELLED`, no eliminado; motivo obligatorio; responsable igual al del cierre; si está eliminado, restaurar primero; si la asignación actual es inválida, la operación exige `assigned_user_id` válido o nulo. |
| Semántica de `assigned_user_id` | Omitido: conservar el responsable si sigue siendo válido; si no, exigir resolución. `null`: dejar sin responsable. ID válido: reasignar al usuario indicado. ID inexistente o no elegible: rechazar la operación. La reasignación y la reapertura son atómicas. |
| Salida | Ticket actualizado; `last_closure` conservado. |
| Errores | `409` (`TICKET_REOPEN_NOT_ALLOWED`, `TICKET_VERSION_CONFLICT`, `TICKET_ASSIGNEE_INVALID`), `422`, `403`. |
| Nota | D-13/D-30: reabren `SUPERUSER`, `ADMINISTRATIVE` y `AREA_MANAGER` según alcance; `AGENT` no. Se conserva el responsable del cierre mientras siga siendo elegible; si no, la reasignación o desasignación es una excepción explícita y no habilita la edición general del ticket cerrado. |

### 11.13 `GET /api/v1/tickets/{id}/receipt`

| Aspecto | Definición |
|---|---|
| Autorización | `P-RECEIPT_DOWNLOAD`; alcance sobre el ticket. |
| Query | `closure_id?`; si se omite, se usa el último `TicketClosure` con comprobante. |
| Salida | Archivo (`application/pdf`), `Content-Disposition: attachment`. |
| Errores | `404` si no hay comprobante o archivo; `403`/`404` si no tiene alcance. |
| Reglas | RN-071 a RN-073. El comprobante pertenece al cierre; se conservan los históricos (D-30). No se audita la descarga (D-22). |

### 11.14 `GET /api/v1/tickets/{id}/history`

| Aspecto | Definición |
|---|---|
| Autorización | `P-TICKET_VIEW_HISTORY`; alcance sobre el ticket. |
| Query | `page`, `page_size`, `sort`. |
| Salida | Entradas de línea de tiempo paginadas. |
| Errores | `404`, `403`. |

### 11.15 `GET /api/v1/tickets/export`

| Aspecto | Definición |
|---|---|
| Autorización | `P-TICKET_EXPORT`; alcance según rol. |
| Query | Mismos filtros que `GET /tickets` + `format=csv` o `format=xlsx`. |
| Salida | Archivo CSV o XLSX, síncrono. |
| Errores | `413` (`EXPORT_TOO_LARGE`), `415`, `403`. |
| Reglas | D-24. |

## 12. Estadísticas

### 12.1 `GET /api/v1/statistics/summary`

| Aspecto | Definición |
|---|---|
| Autorización | `P-STATISTICS_VIEW`; `P-STATISTICS_VIEW_GLOBAL` para global; `P-TICKET_VIEW_DELETED` si se solicita `include_deleted=true` o el KPI `deleted`. |
| Alcance | `S-AREA` o `S-GLOBAL`. |
| Query | `period` (`today`, `range`, `year`, `all`), `date_from`, `date_to`, `area_id`, `type`, `include_deleted`. |
| Salida | KPIs: creados, completados, cancelados, eliminados (sujeto a `P-TICKET_VIEW_DELETED`), por estado, por área y por tipo (`INCIDENT`/`TASK`). |
| Validaciones | Rango válido; `date_from <= date_to`; período permitido. |
| Errores | `422` (`RANGE_INVALID`), `403`. |

### 12.2 `GET /api/v1/statistics/tickets`

| Aspecto | Definición |
|---|---|
| Autorización | Igual que `summary`. |
| Query | `period`, `date_from`, `date_to`, `area_id`, `type`, `group_by` (`status`, `area`, `type`, `date`), `metric` (`created`, `solved`, `cancelled`, `deleted`). |
| Salida | Serie agrupada. |
| Errores | `422`, `403`. |
| Nota | Desglose por tipo aprobado por D-32; no se agrega KPI de reaperturas. |

### 12.3 `GET /api/v1/statistics/export`

| Aspecto | Definición |
|---|---|
| Autorización | `P-STATISTICS_EXPORT`; `P-TICKET_VIEW_DELETED` si la exportación incluye eliminados. |
| Query | Mismos filtros que `summary`/`tickets` + `format=pdf`. |
| Salida | PDF síncrono. |
| Errores | `413`, `415`, `403`. |

## 13. Auditoría

### 13.1 `GET /api/v1/audit-events`

| Aspecto | Definición |
|---|---|
| Autorización | `P-AUDIT_VIEW`; `P-AUDIT_VIEW_GLOBAL` para global. |
| Alcance | `S-AREA` o `S-GLOBAL`. |
| Query | `date_from`, `date_to`, `actor_user_id`, `entity_type`, `entity_id`, `action`, `page`, `page_size`, `sort`. |
| Salida | Eventos de negocio paginados. |
| Errores | `422`, `403`. |
| Nota | No incluye lecturas, descargas ni eventos de seguridad (D-22/D-39). |

### 13.2 `GET /api/v1/audit-events/{id}`

| Aspecto | Definición |
|---|---|
| Autorización | Igual que el listado. |
| Salida | Detalle del evento con valores anteriores/nuevos. |
| Errores | `404` si no existe o está fuera de alcance. |

## 14. Configuración

### 14.1 `GET /api/v1/settings`

| Aspecto | Definición |
|---|---|
| Autorización | `P-SETTINGS_MANAGE`, `S-GLOBAL`. |
| Salida | `timezone`. |
| Errores | `403`. |

### 14.2 `PATCH /api/v1/settings`

| Aspecto | Definición |
|---|---|
| Autorización | `P-SETTINGS_MANAGE`, `S-GLOBAL`. |
| Entrada | `timezone`. |
| Validaciones | Zona IANA válida. |
| Salida | Configuración actualizada. |
| Errores | `422` (`SETTING_INVALID_TIMEZONE`), `403`. |
| Efectos | Audita. |
| Nota | `/settings` sólo expone `timezone` en la primera versión (D-37); no se agregan otros parámetros. |

## 15. Mapeo casos de uso → operaciones

| Caso de uso | Operación(es) |
|---|---|
| UC-AUT-01 | `POST /auth/login`. |
| UC-AUT-02 | `POST /auth/logout`. |
| UC-AUT-03 | `GET /auth/session`. |
| UC-AUT-04 | `GET /me`. |
| UC-AUT-05 | `PATCH /me`. |
| UC-AUT-06 | `PUT /me/password`. |
| UC-ADM-01 | `POST /users`. |
| UC-ADM-02 | `PATCH /users/{id}`. |
| UC-ADM-03 | `PUT /users/{id}/memberships/{membership_id}/role`. |
| UC-ADM-04 | `POST /users/{id}/deactivation`. |
| UC-ADM-05 | `POST /users/{id}/reactivation`. |
| UC-ADM-06 | `PUT /users/{id}/password`. |
| UC-ADM-07 | `GET /roles`, `GET /permissions`. |
| UC-ADM-08 | `GET/POST/PATCH /directions`, `POST /directions/{id}/deactivation`, `POST /directions/{id}/reactivation`. |
| UC-ADM-09 | `GET/POST/PATCH /areas`, `POST /areas/{id}/deactivation`, `POST /areas/{id}/reactivation`. |
| UC-ADM-10 | `GET /audit-events`, `GET /audit-events/{id}`. |
| UC-ADM-11 | `GET /statistics/summary` global. |
| UC-ADM-12 | `GET/PATCH /settings`. |
| UC-TKT-01 | `POST /tickets`. |
| UC-TKT-02 | `GET /tickets`. |
| UC-TKT-03 | `GET /tickets/{id}`. |
| UC-TKT-04 | `PATCH /tickets/{id}`. |
| UC-TKT-05 | `PUT /tickets/{id}/assignment`. |
| UC-TKT-06 | `POST /tickets/{id}/take`. |
| UC-TKT-07 | `POST /tickets/{id}/start`. |
| UC-TKT-08 | `POST /tickets/{id}/completion`. |
| UC-TKT-09 | `POST /tickets/{id}/cancellation`. |
| UC-TKT-10 | `POST /tickets/{id}/deletion`. |
| UC-TKT-11 | `POST /tickets/{id}/restoration`. |
| UC-TKT-12 | `POST /tickets/{id}/reopen`. |
| UC-TKT-13 | `GET /tickets/{id}/receipt`. |
| UC-TKT-14 | `GET /tickets/{id}/history`. |
| UC-REP-01 | `GET /statistics/summary`, `GET /statistics/tickets`. |
| UC-REP-02 | `GET /tickets/export`, `GET /statistics/export`. |
| UC-REP-03 | `GET /audit-events`. |

## 16. Resumen de autorización

| Operación | Permiso | Alcance |
|---|---|---|
| `POST /auth/login` | Público | — |
| `GET /me` | `P-PROFILE_VIEW` | `S-SELF` |
| `PATCH /me` | `P-PROFILE_EDIT` | `S-SELF` |
| `PUT /me/password` | `P-PROFILE_CHANGE_PASSWORD` | `S-SELF` |
| `GET/POST/PATCH /users` | `P-USER_VIEW` / `P-USER_CREATE` / `P-USER_EDIT` | `S-GLOBAL` |
| `POST /users/{id}/deactivation` | `P-USER_DEACTIVATE` | `S-GLOBAL` |
| `POST /users/{id}/reactivation` | `P-USER_REACTIVATE` | `S-GLOBAL` |
| `PUT /users/{id}/memberships/{membership_id}/role` | `P-USER_CHANGE_ROLE` | `S-GLOBAL` |
| `PUT /users/{id}/password` | `P-USER_RESET_PASSWORD` | `S-GLOBAL` |
| `GET /users/assignable` | `P-USER_VIEW_ASSIGNABLE` | `S-AREA` / `S-GLOBAL` |
| `GET /roles`, `GET /permissions` | `P-ROLE_VIEW` | `S-GLOBAL` |
| `GET /directions` | `P-DIRECTION_VIEW` | `S-GLOBAL`; no administradores ven la dirección de sus áreas (D-29) |
| `POST/PATCH /directions` | `P-DIRECTION_MANAGE` | `S-GLOBAL` |
| `GET /areas` | `P-AREA_VIEW` | `S-GLOBAL`; no administradores sólo áreas propias |
| `POST/PATCH /areas` | `P-AREA_MANAGE` | `S-GLOBAL` |
| `GET /tickets` | `P-TICKET_VIEW` | `S-GLOBAL` / `S-AREA` / `S-AGENT_QUEUE` |
| `POST /tickets` | `P-TICKET_CREATE` | `S-GLOBAL` / `S-AREA` |
| `GET /tickets/{id}` | `P-TICKET_VIEW` | Según rol |
| `PATCH /tickets/{id}` | `P-TICKET_EDIT` | `S-GLOBAL` / `S-OWN_CREATED` / `S-AREA` |
| `PUT /tickets/{id}/assignment` | `P-TICKET_ASSIGN` / `P-TICKET_REASSIGN` | `S-GLOBAL` / `S-AREA` |
| `POST /tickets/{id}/take` | `P-TICKET_TAKE` | `S-AGENT_QUEUE` / `S-GLOBAL` (SUPERUSER elegible) |
| `POST /tickets/{id}/start` | `P-TICKET_START` | `S-GLOBAL` / `S-AREA` / `S-OWN_ASSIGNED` |
| `POST /tickets/{id}/completion` | `P-TICKET_COMPLETE` + `P-RECEIPT_UPLOAD` | `S-GLOBAL` / `S-AREA` / `S-OWN_ASSIGNED` |
| `POST /tickets/{id}/cancellation` | `P-TICKET_CANCEL` | `S-GLOBAL` / `S-AREA` / `S-OWN_ASSIGNED` |
| `POST /tickets/{id}/deletion` | `P-TICKET_DELETE` | `S-GLOBAL` / `S-AREA` |
| `POST /tickets/{id}/restoration` | `P-TICKET_RESTORE` | `S-GLOBAL` / `S-AREA` |
| `POST /tickets/{id}/reopen` | `P-TICKET_REOPEN` | `S-GLOBAL` / `S-AREA` |
| `GET /tickets/{id}/receipt` | `P-RECEIPT_DOWNLOAD` | Según rol |
| `GET /tickets/{id}/history` | `P-TICKET_VIEW_HISTORY` | Según rol |
| `GET /tickets/export` | `P-TICKET_EXPORT` | `S-GLOBAL` / `S-AREA` |
| `GET /statistics/*` | `P-STATISTICS_VIEW` / `P-STATISTICS_VIEW_GLOBAL` | `S-AREA` / `S-GLOBAL` |
| `GET /statistics/export` | `P-STATISTICS_EXPORT` | `S-AREA` / `S-GLOBAL` |
| `GET /audit-events` | `P-AUDIT_VIEW` / `P-AUDIT_VIEW_GLOBAL` | `S-AREA` / `S-GLOBAL` / `S-OWN_ASSIGNED` |
| `GET/PATCH /settings` | `P-SETTINGS_MANAGE` | `S-GLOBAL` |

## 17. Decisiones incorporadas

| ID | Decisión | Impacto en el contrato |
|---|---|---|
| D-29 | Dirección derivada del área | Se elimina `affected_direction_id`; la dirección se devuelve derivada. |
| D-30 | Reapertura y cierres | `/reopen` con destino `PENDING`/`IN_PROGRESS`, motivo y mismo responsable; `TicketClosure` y comprobantes históricos. |
| D-31 | Permisos operativos | Autorización de `/start`, `/completion`, `/cancellation`, `/restoration` y `/reopen`. |
| D-32 | Tipos de ticket | Filtros y estadísticas por tipo. |
| D-33 | Gestión de usuarios y autogestión | `/me`, `/users`, membresías y campos editables. |
| D-34 | Campos, estado inicial y motivos | `POST/PATCH /tickets`, `/completion`, `/cancellation`, `/deletion`. |
| D-35 | Listados y filtros | `GET /tickets` con vistas `current`, `completed`, `cancelled`, `deleted` y filtros. |
| D-36 | Último `SUPERUSER` | `/deactivation`, cambio de rol y baja. |
| D-37 | Configuración | `/settings` sólo `timezone`. |
| D-38 | Login/último acceso | `/auth/login`, `/me`, `last_login_at`. |
| D-39 | Logs de seguridad | No expuestos en `/audit-events`; se registran en `SecurityEvent`. |

> Cualquier cambio en estos contratos debe reflejarse en `use-cases.md`, `roles-and-permissions.md` y `open-decisions.md`.
