# Seguridad, sesiones y autorización

> **Fase 1 — Revisión 4.** Incorpora D-07, D-14, D-15, D-16, D-22, D-23, D-27, D-28, D-33, D-36, D-37, D-38 y D-39.
> No se define implementación concreta de ASP.NET Core; se describen requisitos funcionales y de seguridad.

## 1. Decisiones aplicables

| Decisión | Efecto |
|---|---|
| D-07 | Cookie `HttpOnly` + sesión de servidor; 60 minutos deslizantes; invalidación por baja, cambio de rol/área o blanqueo; nuevo blanqueo al reactivar. |
| D-14 | Zona horaria `America/Argentina/Buenos_Aires` por defecto, configurable por el Administrador. |
| D-15 | Legajo inmutable. |
| D-16 | Correo obligatorio e interno. |
| D-22 | Auditoría sólo de cambios de negocio; retención total en principio. |
| D-23 | Política de contraseñas configurable, bloqueo temporal y rate limiting; MFA fuera de alcance. |
| D-27 | ETag/`If-Match`, `409` e `Idempotency-Key` opcional. |
| D-33 | Sólo el `SUPERUSER` gestiona usuarios; autogestión limitada; inactivos excluidos de listados operativos. |
| D-36 | Protección del último `SUPERUSER` activo. |
| D-37 | `/settings` sólo expone `timezone` en la primera versión. |
| D-38 | Login por usuario o correo; registro de `last_login_at`. |
| D-39 | Auditoría de negocio separada de logs de seguridad. |

## 2. Autenticación

### 2.1 Mecanismo aprobado

- Cookie de sesión `HttpOnly`, `Secure` y `SameSite`.
- Sesión de servidor; el cliente no guarda tokens en `localStorage`.
- Expiración deslizante de 60 minutos (D-07).
- No hay expiración absoluta aprobada; no se implementa sin decisión.
- No hay refresh token aprobado; no se implementa sin decisión.

### 2.2 Identificador de login

El login acepta **usuario o correo** (D-38). Preferentemente, el `username` coincide con la parte local del correo. El contrato acepta un `identifier` y resuelve ambos casos; no se distingue en el mensaje de error si el identificador existe.

### 2.3 Flujo

1. El usuario envía identificador y contraseña.
2. El backend busca al usuario por `username` o `email`, verifica el hash y el estado `ACTIVE`.
3. Verifica que tenga al menos una membresía activa.
4. Crea la sesión de servidor y emite cookie `HttpOnly`.
5. Registra `last_login_at`.
6. Devuelve identidad, membresías, roles y permisos efectivos.
7. Cada operación autenticada renueva la marca de actividad.

### 2.4 Errores

- Credenciales inválidas y usuario inactivo comparten el mismo mensaje genérico.
- `401` para no autenticado o sesión expirada.
- `403` para autenticado sin permiso.
- `429` para rate limiting.

## 3. Sesiones

### 3.1 Contenido de la sesión

| Dato | Uso |
|---|---|
| `user_id` | Identidad estable. |
| `session_id` | Identificador de sesión. |
| `issued_at` | Inicio. |
| `last_activity_at` | Expiración deslizante. |
| `expires_at` | 60 minutos desde la última actividad. |
| `membership_version` | Detección de cambios de rol/área. |
| `csrf_token` | Protección CSRF si el mecanismo lo requiere. |

No se guarda el hash ni datos sensibles en la cookie.

### 3.2 Invalidación

La sesión se invalida cuando:

1. el usuario se da de baja;
2. cambia el rol de una membresía;
3. cambia el área de una membresía;
4. se blanquea la contraseña;
5. el usuario se reactiva (se exige nuevo blanqueo);
6. el usuario cierra sesión;
7. expira por inactividad.

### 3.3 Revalidación

En cada operación el backend revalida:

- que la sesión exista y no haya expirado;
- que el usuario esté `ACTIVE`;
- que la membresía siga activa;
- que el rol/área no haya cambiado;
- que el permiso y el alcance cubran la operación.

## 4. Contraseñas

### 4.1 Almacenamiento

- Hash con algoritmo seguro; nunca texto plano.
- El algoritmo y los parámetros se definen en Fase 2.
- No se registra la contraseña en logs.

### 4.2 Política

Configurable (D-23):

- longitud mínima;
- complejidad;
- historial, si se aprueba;
- vigencia, si se aprueba.

La política exacta se define en Fase 2; no se inventan valores.

### 4.3 Blanqueo administrativo

- El `SUPERUSER` ingresa nueva contraseña y confirmación.
- El backend valida coincidencia y política.
- Invalida sesiones del usuario.
- Audita el cambio.

### 4.4 Cambio propio

- D-33 aprueba que el usuario cambie su contraseña.
- Se valida la contraseña actual como control técnico de seguridad en Fase 2.
- Valida política y confirmación.
- Audita el cambio.
- La invalidación de otras sesiones no está aprobada; se mantiene como riesgo residual.

### 4.5 Reactivación

Al reactivar un usuario se exige nuevo blanqueo de contraseña (D-07). No se reutiliza la contraseña anterior.

## 4.6 Seed del `SUPERUSER` inicial

- El seed crea roles, permisos y un `SUPERUSER` inicial para el desarrollador (D-28).
- El usuario inicial se crea con `password_hash` nulo y `password_set_at` nulo; no tiene contraseña utilizable.
- No se genera contraseña fija, predeterminada ni credencial de ejemplo utilizable.
- La autenticación rechaza el login mientras `password_set_at` sea nulo y deriva al procedimiento de blanqueo/activación.
- La cuenta no queda habilitada para operar normalmente hasta establecer una contraseña válida.
- El mecanismo debe impedir que el seed genere credenciales inseguras en producción.

### 4.7 Requisito técnico de activación inicial del `SUPERUSER`

En Fase 2 se deberá implementar un mecanismo seguro para establecer la primera contraseña del `SUPERUSER` inicial:

- token de un solo uso;
- vencimiento definido;
- entrega segura, fuera del código fuente y de credenciales predeterminadas;
- invalidación del token tras su uso o vencimiento;
- registro del evento en `SecurityEvent`.

No se introducen contraseñas fijas ni credenciales de ejemplo utilizables.

## 5. Protección de cuentas

### 5.1 Bloqueo temporal

- Tras intentos fallidos repetidos, bloqueo temporal configurable.
- El contador y la duración se definen en Fase 2.
- El bloqueo no revela si el usuario existe.

### 5.2 Rate limiting

- Límite por IP y por identificador.
- Respuesta `429`.
- Configuración exacta en Fase 2.

### 5.3 MFA

Fuera de alcance inicial (D-23). No se implementa SSO ni autenticación federada.

## 6. Autorización

### 6.1 Principios

1. El backend es la autoridad; la UI sólo anticipa.
2. Se deniega por defecto.
3. Permiso ≠ alcance.
4. El alcance se calcula por membresías activas.
5. Un `SUPERUSER` activo tiene alcance global.
6. Un `AGENT` ve asignados y sin asignar de sus áreas.
7. `ADMINISTRATIVE` edita sólo tickets creados por él.
8. `AREA_MANAGER` opera sobre su área.
9. No se puede dejar el sistema sin `SUPERUSER` activo (D-36).

### 6.2 Evaluación

Para cada operación:

1. autenticar;
2. verificar sesión;
3. verificar permiso;
4. verificar alcance sobre el recurso;
5. verificar estado y versión;
6. ejecutar y auditar si corresponde.

### 6.3 Ocultamiento

Para no revelar existencia de recursos fuera de alcance, se prefiere `404` sobre `403` cuando el recurso existe pero el usuario no debe saberlo. La decisión exacta por endpoint se define en Fase 2.

## 7. Correo y datos de usuario

- El correo es obligatorio (D-16).
- Debe pertenecer a un dominio interno configurado.
- Es único.
- El legajo es único e inmutable (D-15).
- El nombre de usuario es único (Q-013).
- El login acepta usuario o correo (D-38).
- El usuario edita nombre, apellido, correo y contraseña; no rol, áreas, legajo ni `username` (D-33).
- Los usuarios inactivos conservan historial y tickets y se excluyen de listados operativos (D-33).

## 8. Fechas y zona horaria

- Almacenamiento en UTC.
- Zona de negocio: `America/Argentina/Buenos_Aires` por defecto.
- El Administrador puede cambiarla (D-14).
- Los filtros por día y los KPIs usan la zona configurada.
- El cambio se audita.
- `/settings` sólo expone `timezone` en la primera versión (D-37).

## 9. Auditoría y logs

### 9.1 Auditoría de negocio

Se auditan:

- altas, ediciones y bajas de usuarios;
- cambios de membresía/rol;
- altas/ediciones de direcciones y áreas;
- creación, edición, asignación, reasignación, toma, inicio, completion, cancelación, reapertura, eliminación y restauración de tickets;
- cambios de configuración.

No se auditan lecturas ni descargas (D-22).

### 9.2 Retención

Se retiene toda la auditoría de negocio en principio (D-22). La política de purga no está definida.

### 9.3 Logs de seguridad

Login, logout, intentos fallidos, bloqueos, rate limiting e invalidaciones de sesión se registran en `SecurityEvent`, separados de la auditoría de negocio. No se exponen por `/audit-events` (D-39).

## 10. Concurrencia e idempotencia

- `version`/ETag en usuarios, direcciones, áreas y tickets.
- `If-Match` en escrituras.
- `409` si la versión no coincide.
- `Idempotency-Key` opcional en altas (D-27).
- Las transiciones no son idempotentes.

## 11. Transporte y CSRF

- HTTPS obligatorio.
- Cookie `Secure`, `HttpOnly`, `SameSite`.
- CORS restringido al origen del frontend.
- Protección CSRF: se requiere; el mecanismo exacto (token anti-CSRF, `SameSite`) se define en Fase 2.
- No se aceptan credenciales en query string.

## 12. Riesgos residuales

| Riesgo | Impacto | Tratamiento |
|---|---|---|
| Sin expiración absoluta | Medio: sesión prolongada con actividad. | Evaluar en Fase 2; no inventar. |
| Logs de seguridad sin política de retención | Medio: operación y auditoría. | Definir política operativa en Fase 2. |
| Correo interno sin lista de dominios | Medio: validación. | Definir dominios en Fase 2. |
| CSRF sin mecanismo definido | Alto: seguridad. | Definir en Fase 2. |
| Política de contraseñas sin valores | Medio: seguridad. | Configurar en Fase 2. |
| Invalidación de otras sesiones al cambiar contraseña | Bajo/medio: seguridad. | Definir en Fase 2; no está aprobada. |

## 13. Decisiones incorporadas

No quedan decisiones pendientes que afecten seguridad. D-33, D-36, D-37, D-38 y D-39 están incorporadas; los riesgos residuales están en [`open-decisions.md`](./open-decisions.md).

## 14. Trazabilidad

| Decisión | Dónde se refleja |
|---|---|
| D-07 | §2, §3, §4.5. |
| D-14 | §8. |
| D-15 | §7. |
| D-16 | §7. |
| D-22 | §9. |
| D-23 | §4, §5. |
| D-27 | §10. |
| D-33 | §4.4, §7, §9. |
| D-36 | §6.1. |
| D-37 | §8. |
| D-38 | §2.2, §2.3, §7. |
| D-39 | §9.3. |
| Q-001 | §3.2, §4.5. |
| Q-002 | §6. |
| Q-006 | §4.4. |
| Q-009 | §4.3. |
| Q-012 | §3.1. |
| Q-013 | §7. |
| Q-026 | §8. |
