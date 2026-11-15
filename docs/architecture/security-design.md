# Diseño de seguridad — Sistema de Gestión de Tickets

> **Incremento F2-I1 — Decisiones arquitectónicas.**
> **Estado: cerrado.**
> Traducción de requisitos de Fase 1 a diseño técnico para ASP.NET Core.  
> Clasificación: `[A]` decisión aprobada, `[P]` propuesta técnica, `[?]` pendiente, `[R]` riesgo.

## 1. Fuentes

`docs/definitions/specification-definitions.md`, `docs/specification/security-and-sessions.md`, `roles-and-permissions.md`, `api-contracts.md`, `domain-model.md`.  
La documentación funcional de Fase 1 está finalizada y se usa como fuente funcional de verdad. Este incremento no la modifica.

## 2. Requisitos aprobados (resumen)

D-07, D-14, D-15, D-16, D-22, D-23, D-27, D-33, D-36, D-37, D-38, D-39 (ver `architecture-decisions.md` ADR-004/005/010/011).

## 3. Autenticación

### 3.1 Mecanismo [A]

Cookie `HttpOnly`, `Secure`, `SameSite`; sesión de servidor; 60 minutos deslizantes; sin JWT en navegador ni tokens en `localStorage`; sin expiración absoluta ni refresh tokens.

### 3.2 Identificador [A]

Login por usuario o correo (`identifier`), sin revelar existencia.

### 3.3 Flujo [A]

Buscar por `username`/`email`, verificar hash y estado `ACTIVE`, verificar membresía activa, crear sesión, emitir cookie, registrar `last_login_at`, devolver identidad/permisos.

## 4. Sesiones de servidor

### 4.1 Contenido [A]

`user_id`, `session_id`, `issued_at`, `last_activity_at`, `expires_at`, `membership_version`, `csrf_token`. Sin hash ni datos sensibles en la cookie.

### 4.2 Invalidación [A]

Baja, cambio de rol/área, blanqueo, reactivación, logout, expiración por inactividad.

### 4.3 Revalidación [A]

Sesión existente/no expirada, usuario `ACTIVE`, membresía activa, rol/área sin cambios, permiso y alcance.

### 4.4 Estrategia aprobada [A] (ADR-018, PD-001)

- Usar autenticación por cookies de ASP.NET Core con un `ITicketStore` de servidor persistido en PostgreSQL.  
- La cookie del navegador contiene únicamente el identificador/referencia de la sesión; la fuente de verdad es el almacén servidor.  
- Tabla `user_sessions` (o equivalente) con: `session_id`, `user_id`, `issued_at`, `last_activity_at`, `expires_at`, `membership_version`, `csrf_token`, `is_revoked`, ticket cifrado.  
- Expiración deslizante de 60 minutos: en cada solicitud autenticada, si hay actividad, actualizar `last_activity_at` y `expires_at` en el almacén y la expiración de la cookie.  
- Revocación inmediata: marcar `is_revoked` o eliminar la sesión; la validación en cada solicitud rechaza sesiones inexistentes, expiradas o revocadas.  
- Invalidación por baja, cambio de rol/área y blanqueo: revocar sesiones del usuario (o comparar `membership_version`).  
- Logout: eliminar/revocar la sesión y limpiar la cookie.  
- Limpieza de sesiones expiradas mediante tarea en segundo plano.  
- `membership_version`: valor almacenado en `User`, actualizado cuando cambian membresías/roles; la sesión guarda el valor al iniciar y cada solicitud lo compara para detectar cambios.

## 5. Cookies y atributos

### 5.1 Requisitos [A]

`HttpOnly`, `Secure`, `SameSite`, sin credenciales en query string.

### 5.2 Propuesta [P]

| Atributo | Valor | Notas |
|---|---|---|
| HttpOnly | true | [A] |
| Secure | true (Always) | [A] |
| SameSite | Lax | [A] (ADR-016, PD-003) |
| Path | / | [P] |
| Domain | omitido (host-only) | [A] (ADR-017, PD-005) |
| IsEssential | true | [P] |

Cookie host-only salvo necesidad documentada de compartirla entre subdominios.

## 6. Contraseñas

### 6.1 Requisitos [A]

Hash seguro, sin texto plano y sin registro en logs; política configurable (D-23); blanqueo administrativo; cambio propio y reactivación con nueva contraseña provisional.

### 6.2 Aprobada [A] (ADR-P-002, PD-002)

`PasswordHasher` de ASP.NET Core (PBKDF2-HMAC-SHA256, ~600 000 iteraciones) [A] (ADR-P-002, PD-002); Argon2id descartado. Validación mediante servicio de aplicación con parámetros configurables. La contraseña provisional se almacena hasheada como cualquier otra contraseña.

### 6.3 Bootstrap del SUPERUSER inicial [A] (ADR-019, PD-006)

El seed crea una `Direction` y un `Area` mínimas, junto con una `UserAreaMembership` activa para la cuenta bootstrap `superuser@organizacion`. La cuenta conserva una membresía activa durante su ciclo de vida; no se adopta una excepción de membresía para `SUPERUSER`.

El `SUPERUSER` recibe una contraseña provisional aleatoria, generada por el sistema, de un solo uso y válida durante 72 horas. En su primer acceso sólo puede cambiarla obligatoriamente; hasta completar ese cambio no puede acceder a ninguna otra función. La contraseña provisional se invalida tras usarse o al vencer.

## 7. Fuerza bruta

### 7.1 Requisitos [A]

Bloqueo temporal, rate limiting por IP/identificador, `429`, sin revelar existencia, mensaje genérico para credenciales inválidas/usuario inactivo.

### 7.2 Propuesta [P]

Middleware `AddRateLimiter`, contador de intentos por identificador, bloqueo temporal configurable, registro en `SecurityEvent`.

## 8. Autorización y alcance

### 8.1 Requisitos [A]

Cuatro roles fijos, catálogo fijo, `SUPERUSER` global, multi-área, permisos declarativos, protección del último `SUPERUSER`, gestión de usuarios por `SUPERUSER`.

### 8.2 Propuesta [P]

Políticas de ASP.NET Core con handlers por permiso; `ICurrentUserContext` con membresías/permisos/alcance; atributos/filtros de endpoint; verificación de alcance en aplicación.

### 8.3 Deny-by-default técnico [A] (ADR-015, PD-020)

- Política de fallback que exige usuario autenticado y metadato de permiso en el endpoint.  
- Endpoints protegidos declaran explícitamente el permiso (`[Authorize(Policy=...)]` o `RequireAuthorization`).  
- Endpoints públicos se declaran explícitamente anónimos.  
- Ausencia de metadatos de autorización provoca denegación.  
- Pruebas automatizadas enumeran endpoints y verifican metadatos.  
- Alcance validado en capa de aplicación, no sólo en API.

Se diferencia: autenticación (identidad/sesión), autorización por permiso (acción) y autorización por alcance (datos).

## 9. Validación de permisos y alcance

Flujo: autenticar → verificar sesión → verificar permiso → verificar alcance → verificar estado/versión → ejecutar y auditar.  
Implementación mediante middleware que carga contexto, filtro de autorización y servicios de aplicación.

## 10. CSRF [A] (ADR-016, PD-003)

Estrategia única:

- `SameSite=Lax`.  
- Cookie `Secure` y host-only.  
- Para métodos que modifican estado (POST, PUT, PATCH, DELETE):  
  - Validar header `Origin` contra lista blanca de orígenes frontend. Si `Origin` está presente y no coincide, rechazar (`403 CSRF_VALIDATION_FAILED`).  
  - Si `Origin` no está presente, no se infiere confianza a partir de `Host`. La solicitud se rechaza (`403 CSRF_VALIDATION_FAILED`), salvo que incluya un token anti-CSRF válido emitido por la aplicación para una sesión anónima/pre-sesión (ver protección de login). En la práctica, el frontend siempre envía `Origin` en solicitudes cross-origin; si falta, se rechaza.  
  - Validar token anti-CSRF en header `X-CSRF-TOKEN` contra el token de sesión.  
- `Referer` no es el control principal; puede registrarse para diagnóstico.  
- Token CSRF:  
  - Se obtiene mediante `GET /api/v1/auth/csrf`. Antes de autenticarse, el endpoint opera en modo anónimo y emite un token pre-sesión de corta duración; tras autenticarse, el token se vincula a la sesión.  
  - Se almacena en memoria en el frontend (no en `localStorage`).  
  - Se envía en `X-CSRF-TOKEN` en operaciones inseguras.  
- Protección del login contra login-CSRF: el endpoint de login es público, pero exige `Content-Type: application/json` (lo que dispara preflight de CORS) y valida `Origin` si está presente; si `Origin` falta, exige un token pre-sesión válido. Tras autenticación exitosa, el token se rota a uno vinculado a la sesión. Las publicaciones simples de formulario cross-site se rechazan por ausencia de `Origin` válido o de token pre-sesión.  
- Cargas multipart: mismo token en header `X-CSRF-TOKEN`.  
- Respuesta ante fallo: `403` con código `CSRF_VALIDATION_FAILED`.  
- CORS restringido y `AllowCredentials=true`.

## 11. CORS y topología de desarrollo [A] (ADR-017, PD-005)

### 11.1 Desarrollo

- Frontend: `https://localhost:5173`  
- Backend: `https://localhost:5001`  
- CORS: origen exacto `https://localhost:5173`, credenciales habilitadas.  
- Cookie: `Secure=true`, `SameSite=Lax`, host-only.  
- React: `fetch(..., { credentials: 'include' })`.  
- Certificados HTTPS de desarrollo confiados (`dotnet dev-certs https --trust`, Vite HTTPS).

### 11.2 Producción

- Orígenes exactos aprobados, HTTPS, `Secure=true`, sin comodines con credenciales.

### 11.3 Atributo Domain

Omitir `Domain` (host-only) salvo necesidad concreta y documentada.

## 12. Concurrencia [A]/[P]

Ver ADR-011: recursos versionados, `If-Match` obligatorio, falta → `428`, desactualizado → `409`, ETag fuerte, `version` en cuerpo sólo salida.

## 13. Secretos

User Secrets en desarrollo, variables de entorno/proveedor en producción, sin secretos en repositorio, configuración tipada validada al inicio.

## 14. Auditoría y logs de seguridad

### 14.1 Requisitos [A]

Auditoría de negocio (cambios) sin lecturas/descargas; logs de seguridad en `SecurityEvent`; no expuestos por `/audit-events`; retención total en principio.

### 14.2 Propuesta [P]

`IAuditService` y `ISecurityEventService` separados; auditoría en la misma transacción de negocio cuando sea posible; logs de seguridad asíncronos si es necesario.

## 15. Errores de autenticación/autorización

Mensajes genéricos, `401`/`403`/`429`, formato único con `code`, `correlation_id`, `timestamp`, sin stack traces en producción. La preferencia `404` vs `403` por endpoint se rige por visibilidad legítima (PD-020; ver `docs/definitions/architecture-definitions.md`).

La matriz se aplica después de validar el formato de la solicitud y antes de las reglas de negocio. Los errores de estado, versión, duplicidad o validación conservan los códigos definidos en `api-contracts.md` —por ejemplo, `409` para una transición de ticket no permitida o una versión desactualizada— y no se sustituyen por `403`.

| Endpoint o grupo | Sin sesión válida | Sin permiso para el endpoint | Recurso fuera de la visibilidad legítima | Recurso visible, pero operación no autorizada |
|---|---|---|---|---|
| `POST /api/v1/auth/login` | `401` con el mismo código `AUTH_INVALID_CREDENTIALS` y mensaje genérico para identificador inexistente, contraseña inválida, usuario inactivo o sin membresía activa; `429` ante límite de intentos. | No aplica. | No aplica. | No aplica. |
| `GET /api/v1/auth/csrf` | No aplica: es público. | No aplica. | No aplica. | No aplica. |
| `POST /api/v1/auth/logout`, `GET /api/v1/auth/session` y perfil propio (`/me`) | `401`. | No aplica para una sesión válida y su propio perfil. | No aplica: sólo tratan la sesión o el recurso propio. | No aplica. |
| `GET /api/v1/users`, altas y operaciones globales de usuarios, membresías o blanqueo | `401`. | `403`. | No aplica: sólo `SUPERUSER` puede operar sobre usuarios. | No aplica. |
| `GET /api/v1/users/{id}` | `401`. | `403` si falta `P-USER_VIEW`. | `404` si el usuario no existe. | No aplica: quien posee el permiso tiene alcance global. |
| `GET /api/v1/users/assignable` | `401`. | `403` si falta `P-USER_VIEW_ASSIGNABLE`. | El resultado se limita al área autorizada; no revela candidatos de otras áreas. | No aplica. |
| Catálogos de roles y permisos (`GET /roles`, `GET /permissions`) | `401`. | `403` si falta `P-ROLE_VIEW`. | No aplica: son catálogos completos. | No aplica. |
| Listados de direcciones y áreas | `401`. | `403` si falta `P-DIRECTION_VIEW` o `P-AREA_VIEW`. | El listado se limita al alcance del usuario; no revela elementos fuera de él. | No aplica. |
| Detalle de dirección o área (`GET /directions/{id}`, `GET /areas/{id}`) | `401`. | `403` si falta el permiso de consulta. | `404` si no existe o no integra las áreas/direcciones visibles para el usuario. | No aplica. |
| Gestión de direcciones o áreas (alta, edición, activación y desactivación) | `401`. | `403` si falta el permiso de gestión. | `404` si se identifica una entidad fuera de la visibilidad legítima. | `403` si la entidad es visible, pero el actor no puede gestionarla. |
| `GET /api/v1/tickets` y `GET /api/v1/tickets/export` | `401`. | `403` si falta el permiso de consulta o exportación, incluido `P-TICKET_VIEW_DELETED` cuando corresponde. | El resultado se limita al alcance; los tickets fuera de él no se revelan. | No aplica. |
| `POST /api/v1/tickets` | `401`. | `403` si falta `P-TICKET_CREATE`. | `404` si el `area_id` no existe o no pertenece al alcance de creación; ambas situaciones reciben la misma respuesta para no revelar la existencia del área. Un área visible pero inactiva conserva el error de validación definido por el contrato. | No aplica. |
| `GET /api/v1/tickets/{id}` | `401`. | `403` si falta `P-TICKET_VIEW`. | `404` si no existe, está eliminado sin `P-TICKET_VIEW_DELETED` o está fuera de alcance. | No aplica. |
| Operaciones de escritura sobre un ticket existente: `PATCH`, asignación, toma, inicio, completado, cancelación, eliminación, restauración y reapertura | `401`. | `403` si falta el permiso de la acción. | `404` si el ticket no existe o está fuera de la visibilidad legítima. | `403` si el ticket es visible, pero el actor no tiene el alcance operativo de esa acción. Las reglas de ciclo de vida y concurrencia conservan `409`; las validaciones de entrada conservan sus códigos específicos. |
| Comprobante e historial de ticket (`GET /tickets/{id}/receipt`, `GET /tickets/{id}/history`) | `401`. | `403` si falta `P-RECEIPT_DOWNLOAD` o `P-TICKET_VIEW_HISTORY`. | `404` si el ticket no existe o está fuera de alcance. Para un comprobante, también si el cierre solicitado no tiene `TicketReceipt`. | `403` si el ticket es visible, pero falta el alcance para descargar el comprobante o consultar el historial. |
| Estadísticas y exportación de estadísticas | `401`. | `403` si falta el permiso de estadísticas, de exportación, global o de consulta de eliminados requerido por la solicitud. | No aplica: no se consulta un recurso individual; los resultados se calculan dentro del alcance autorizado. | No aplica. |
| `GET /api/v1/audit-events` | `401`. | `403` si falta `P-AUDIT_VIEW` o `P-AUDIT_VIEW_GLOBAL` cuando se solicita alcance global. | El listado se limita al alcance autorizado: global, área o tickets propios asignados. | No aplica. |
| `GET /api/v1/audit-events/{id}` | `401`. | `403` si falta el permiso de auditoría. | `404` si el evento no existe o está fuera del alcance de auditoría del actor. | No aplica. |
| `GET` y `PATCH /api/v1/settings` | `401`. | `403` si falta `P-SETTINGS_MANAGE`. | No aplica: la configuración es global y no se expone parcialmente. | No aplica. |

### 15.1 Tickets: autorización, ciclo de vida y concurrencia

- `403` deniega una acción por ausencia de permiso o alcance. Incluye al `AGENT` que intenta iniciar, completar o cancelar un ticket no asignado a él, y a cualquier actor que intenta una acción no habilitada por su rol.
- `409` representa un conflicto del recurso ya autorizado: edición, asignación o toma fuera de `PENDING`/`IN_PROGRESS`; transición desde un estado no permitido; toma de un ticket ya asignado; eliminación de uno ya eliminado; restauración de uno no eliminado; reapertura de uno no cerrado o eliminado; o conflicto de versión. En restauración o reapertura, una asignación actual inválida conserva el `409` definido para `TICKET_ASSIGNEE_INVALID`.
- La asignación o reasignación valida que el responsable sea elegible; los errores de datos de entrada conservan los códigos de validación del contrato. No convierten una falta de permiso o alcance en `409`.

### 15.2 Comprobantes

- `404` indica que el ticket o cierre solicitado no tiene comprobante asociado, o que el ticket está fuera de alcance.
- `500` con código `RECEIPT_STORAGE_INTEGRITY_FAILURE` indica que existe un `TicketReceipt`, pero falta su archivo físico o su checksum no coincide. Es una falla de integridad interna, no una ausencia legítima del comprobante.
- `503` indica que el almacenamiento de comprobantes está temporalmente indisponible. No se usa para la ausencia del archivo ni para una falla de integridad comprobada.

`404` no revela la existencia de un recurso individual fuera de alcance. `403` se usa para denegar una capacidad del endpoint o una operación sobre un recurso cuya existencia ya es visible para el actor. Los eventos de seguridad nunca se exponen por `/api/v1/audit-events`.

## 16. Archivos protegidos

Almacenamiento fuera de raíz web, clave UUID, validación MIME/tamaño, checksum, metadatos en `TicketReceipt`, descarga autenticada, sin URL estática, sin auditoría de descarga, escaneo/purga opcionales.

## 17. Riesgos y pendientes

Ver `pending-decisions.md`. Riesgos vigentes: sin expiración absoluta, MFA fuera de alcance y refresh tokens no aprobados.

## 18. Trazabilidad

D-07→§3/§4/§5; D-14→zona horaria; D-15→legajo; D-16→correo; D-22→§14; D-23→§6/§7; D-27→§12; D-33→§8; D-36→§8; D-37→configuración; D-38→§3.2; D-39→§14; Q-001→§4.2; Q-002→§8; Q-012→§4.1; Q-013→§6; Q-019→§16; Q-026→zona horaria.
