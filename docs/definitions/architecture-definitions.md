# Definiciones arquitectónicas — Fase 2

> Registro de las definiciones técnicas PD-001 a PD-021 de `docs/architecture/pending-decisions.md` aprobadas explícitamente. Todas tienen estado **[A]**.
>
> Este archivo es la resolución de referencia; `pending-decisions.md` queda como registro de estado que apunta aquí. Los ADRs de `docs/architecture/` conservan el detalle técnico y citan estas definiciones cuando corresponde.
>
> Las decisiones de Fase 2 prevalecen sobre las de Fase 1 y Fase 0; las de Fase 1 prevalecen sobre las de Fase 0. Las decisiones de Fase 1 (D-xx/Q-xx) siguen en `docs/definitions/specification-definitions.md` y no se modifican aquí.

## Resumen

| ID | Definición | Responder antes de | Resolución (una línea) |
|---|---|---|---|
| PD-001 | Almacén de sesiones | F2-I1 | PostgreSQL: tabla `user_sessions` detrás de `ITicketStore` (ADR-018). |
| PD-002 | Hash de contraseñas | F2-I4 | `PasswordHasher` de ASP.NET Core: PBKDF2-HMAC-SHA256, ~600.000 iteraciones (ADR-P-002). |
| PD-003 | Mecanismo CSRF | F2-I1 | ADR-016: `SameSite=Lax` + validación de `Origin` + token `X-CSRF-TOKEN` ligado a sesión. |
| PD-004 | Claves Data Protection | F2-I1 | Persistir en PostgreSQL (provider EF Core), rotación automática. |
| PD-005 | Topología dev HTTPS | F2-I1 | ADR-017: HTTPS en dev (front 5173, back 5001), cookie host-only con `Secure`. |
| PD-006 | Bootstrap SUPERUSER | F2-I1 | Seed organizacional mínimo; cuenta `superuser@organizacion` permanente (extiende D-28, ADR-019). |
| PD-007 | Primera contraseña del usuario | F2-I4 | Contraseña provisional de un solo uso (12 caracteres, 72 h) con cambio obligatorio; sin SMTP, sin token, sin `/activar`. |
| PD-008 | Versión Node.js | F2-I1 | Node.js 24 LTS o superior, declarado mediante `.nvmrc` + `engines`. |
| PD-009 | Gestor de paquetes | F2-I1 | pnpm vía `corepack enable`. |
| PD-010 | Framework de pruebas frontend | F2-I2 | Vitest + React Testing Library + jsdom, coverage V8. |
| PD-011 | Versión PostgreSQL | F2-I1 | PostgreSQL 18.x, tag fijado en `docker-compose`. |
| PD-012 | Umbrales de bloqueo | F2-I4 | Fijos: 3 intentos por identificador y 5 por IP (15 min), `429` con `Retry-After`. |
| PD-013 | Política de contraseñas | F2-I4 | Defaults NIST (mín. 12) + parámetros configurables por SUPERUSER, no retroactivo. |
| PD-014 | Dominios de correo interno | F2-I4 | Allowlist en `SystemSetting` (SUPERUSER); seed con `organizacion`. |
| PD-015 | Sesiones al cambiar contraseña | F2-I4 | Invalidar todas las demás sesiones del usuario. |
| PD-016 | Edición de `username` | F2-I4 | Inmutable; se deriva del correo institucional en el alta. |
| PD-017 | Retención de logs de seguridad | F2-I5 | Retención completa sin purga en v1 (alineada con D-22). |
| PD-018 | Tamaño de comprobantes | F2-I5 | Máximo 10 MB, validación server-side, configurable por SUPERUSER. |
| PD-019 | Almacenamiento de comprobantes | F2-I5 | Directorio fuera de raíz web + backup BD + carpeta; no `bytea`. |
| PD-020 | 404 vs 403 por endpoint | F2-I1 | Matriz por visibilidad legítima (401 → 404 default → 403 si la existencia es conocida). |
| PD-021 | Versiones de SDK/Node | F2-I1 | SDK fijado por `global.json`; Node.js 24 LTS o superior declarado por `.nvmrc` + `engines`; PostgreSQL por tag. |

## Resoluciones por incremento

### F2-I1 — Decisiones de arquitectura y stack

#### PD-001 — Almacén de sesiones
- **Decisión:** sesiones en PostgreSQL: tabla `user_sessions` detrás de `ITicketStore` (base de ADR-018). Se persiste una fila por login; `membership_version` habilita invalidación en bloque ante cambios de roles/permisos; la cookie guarda solo el token de sesión, no la sesión serializada.
- **Justificación:** usa el stack aprobado sin componentes nuevos; sesiones consultables e invalidables por usuario; sobrevive reinicios sin configuración adicional. El costo de validar en cada request es irrelevante a esta escala con índice por token.
- **Alternativa descartada:** caché distribuida (Redis/`IDistributedCache`): agrega infraestructura adicional para una necesidad que PostgreSQL cubre con menor complejidad operativa. La invalidación por usuario es posible con ambas alternativas.

#### PD-003 — Mecanismo CSRF (ADR-016)
- **Decisión:** cookie de sesión `SameSite=Lax`; validación de `Origin`/`Referer` contra allowlist absoluta (nunca confiar en `Host`); token CSRF en header `X-CSRF-TOKEN`, sin estado, ligado a la sesión (HMAC sobre el ID de sesión con clave de Data Protection); distribución vía `GET /api/v1/auth/csrf` con token pre-sesión para el login y mitigación de login-CSRF.
- **Alternativas descartadas:** token server-side por sesión en `user_sessions` (acopla CSRF a la tabla de sesiones y no cubre pre-sesión) y `SameSite=Strict` solo (rompe navegaciones entrantes legítimas y no cubre login-CSRF).

#### PD-004 — Persistencia de claves Data Protection
- **Decisión:** claves persistidas en PostgreSQL con el provider EF Core de ASP.NET Core Data Protection (tabla `DataProtectionKeys`), rotación automática configurada en `KeyManagementOptions`.
- **Justificación:** sobreviven a reinicios y deploys sin pasos manuales; cero infraestructura extra; el despliegue siempre conecta a la BD.
- **Alternativas descartadas:** claves en filesystem/volumen (frágil ante deploys y requiere compartición manual entre instancias) y default efímero (invalida todas las sesiones y tokens en cada deploy).

#### PD-005 — Topología HTTPS en desarrollo (ADR-017)
- **Decisión:** HTTPS en dev para frontend (`https://localhost:5173`) y backend (`https://localhost:5001`); cookie host-only con atributo `Secure` real desde el día uno, sin banderas dev/prod de cookie. Backend con `dotnet dev-certs https --trust`; frontend Vite con certificado dev.
- **Justificación:** con CSRF basado en `Origin` + cookie `Secure`, el entorno de desarrollo debe reproducir la validación real; aceptar certificados es costo de una vez por desarrollador.
- **Alternativa descartada:** HTTP en dev (obliga a apagar `Secure` en dev e impide probar la validación de `Origin` en condiciones reales).

#### PD-006 — Bootstrap del SUPERUSER inicial (extiende D-28; ADR-019)
- **Decisión:** seed organizacional mínimo: el seed crea una `Direction` y una `Area` mínimas (convención documentada) y la `UserAreaMembership` activa para la cuenta bootstrap. La cuenta es explícita: **`superuser@organizacion`** (identificador de login del seed).
- **Operación:** la cuenta del desarrollador **no se da de baja nunca**: permanece activa como respaldo durante todo el ciclo de vida de la organización. Tras la activación, el SUPERUSER puede reorganizar la estructura real y (si lo necesita) dar de baja lógica los registros organizacionales del seed, dejando los registros "iniciales" como trazabilidad.
- **Consecuencia registrada:** para que la cuenta siga siendo usable, su membresía debe permanecer activa (mantener el área seed activa o reasignar la membresía si se reorganiza la estructura); no se elimina nada del seed sin otra membresía activa del SUPERUSER que lo ampare.
- **Alternativa descartada:** excepción de membresía para SUPERUSER (rompe la invariante de login por membresía y obliga a casos especiales en autorización).

#### PD-008 — Versión de Node.js
- **Decisión:** Node.js **24 LTS o superior**, declarado mediante `.nvmrc` + `engines`; no se fija una versión de parche.
- **Alternativa descartada:** Node 22 LTS (ya en fase de mantenimiento, ventana de soporte más corta).

#### PD-009 — Gestor de paquetes frontend
- **Decisión:** **pnpm**, por velocidad y disciplina de instalación/disco frente a npm. Se activa vía `corepack enable` (sin instalación global separada) en el entorno reproducible.
- **Alternativa descartada:** npm (incluso por venir incluido con Node).

#### PD-011 — Versión de PostgreSQL
- **Decisión:** PostgreSQL **18.x** (rama madura, soporte hasta ~2030, soportada por Npgsql/EF Core sin configuración adicional); versión exacta fijada por tag de imagen en el `docker-compose` de F2-I2/F2-I3.
- **Alternativa descartada:** PostgreSQL 16.x (propuesta original, con ventana de soporte ~2 años menor).

#### PD-020 — 404 vs 403 por endpoint (ADR-015)
- **Decisión:** regla por visibilidad legítima + matriz detallada por endpoint en `security-design.md` §15: primero `401` sin sesión válida; `404` (default, deny-by-default de ADR-015) cuando la existencia del recurso no es parte de la visibilidad legítima del usuario, respuesta idéntica a recurso inexistente; `403` cuando el usuario ya está autorizado a saber que existe pero no a operar en esa acción, con respuesta genérica `FORBIDDEN`. Las reglas de estado, versión y validación conservan sus códigos específicos.
- **Alternativas descartadas:** `403` uniforme (filtra existencia y habilita enumeración) y `404` uniforme (experiencia confusa en operaciones legítimas de la UI).

#### PD-021 — Versiones de SDK y Node
- **Decisión:** SDK **10.0.403** vía `global.json` (backend), Node.js 24 LTS o superior vía `.nvmrc`/`engines` (PD-008) y PostgreSQL vía tag de `docker-compose` (PD-011). Los parches del SDK y de los paquetes son mantenimiento de rutina, no requieren decisión de usuario.

### F2-I2 — Estructura de la solución

#### PD-010 — Framework de pruebas frontend
- **Decisión:** **Vitest** (ecosistema nativo de Vite, API compatible con Jest, TypeScript de primera clase), complementado con React Testing Library + jsdom y coverage con provider V8.
- **Alternativa descartada:** Jest (duplica el pipeline de transform respecto de Vite, corridas más lentas y fricciones con ESM).

### F2-I4 — Autenticación, sesiones y contraseñas

#### PD-002 — Algoritmo de hash de contraseñas (ratifica ADR-P-002)
- **Decisión:** `PasswordHasher<T>` de ASP.NET Core Identity, PBKDF2-HMAC-SHA256 con `IterationCount` elevado a ~600 000 (guía OWASP). El formato V3 con metadato de iteraciones permite rehash-on-login ante futuras subidas de parámetros o cambio de esquema.
- **Alternativa descartada:** Argon2id third-party (memory-hard, pero dependencia de librería externa en criptografía y sin migración de esquema nativa; el riesgo restante se mitiga con lockout + rate limiting + política de contraseña).

#### PD-007 — Primera contraseña del usuario (activación y restauración)
- **Decisión:** sin SMTP, sin token de activación y sin página `/activar`. Contraseña provisional de un solo uso: **aleatoria de 12 caracteres generada por el sistema** (nunca elegida por el SUPERUSER), almacenada hasheada como cualquier contraseña; el SUPERUSER solo la ve una vez al generarla.
- **Entrega:** manual por canales internos (puede ser correo electrónico escrito a mano, sin integración).
- **Procedimiento único** válido para activación inicial y restauración de contraseña olvidada (reporte fuera de la app → SUPERUSER crea/blanquea → entrega manual → login).
- **Login con la provisional:** la sesión queda con permisos restringidos a la pantalla de cambio de contraseña (nada más hasta cambiarla); la provisional no vuelve a servir en el siguiente login.
- **Vencimiento:** 72 h sin uso; un solo credencial pendiente por cuenta (re-generar invalida el anterior).
- **No agrega endpoints ni páginas nuevas** (reutiliza login + cambio de contraseña propia + bloqueo/rate limiting).
- **Prevalencia:** esta decisión de Fase 2 prevalece explícitamente sobre el mecanismo de token de un solo uso descrito en la documentación de Fase 1 (`security-and-sessions.md` §4.7, RN-114 y `open-decisions.md`). Los documentos de Fase 1 conservan el estado de sus decisiones al cierre de esa fase.
- **Alternativas descartadas:** URL con token entregada manualmente y activación self-service con página `/activar`.

#### PD-012 — Umbrales de bloqueo y rate limiting
- **Decisión:** umbrales fijos endurecidos: 3 intentos fallidos por identificador (usuario/correo) → bloqueo 15 min; 5 intentos fallidos por IP en ventana de 15 min → `429` con `Retry-After`. Contadores se limpian con login exitoso. Valores en constantes de código documentadas (no BD en v1).
- **Alternativa descartada:** escalado progresivo (complejidad extra sin necesidad a esta escala).

#### PD-013 — Política de contraseñas: valores y configuración
- **Decisión:** defaults = Opción A (NIST): mínimo 12, máximo 64, composición forzada desactivada (toggles de mayúscula/dígito/símbolo), lista de bloqueo de contraseñas comunes activada (lista offline fija incluida en código, no configurable), rotación periódica forzada desactivada (0 días = off).
- **Configurable por SUPERUSER** vía pantalla/endpoints exclusivos (ej. `/api/v1/admin/password-policy`, separado del `/settings` de RN-111 que solo expone `timezone`): longitud mínima (1–64), longitud máxima, toggles de composición, vencimiento de la contraseña provisional en horas (default 72, PD-007) y días de rotación periódica (0 = off).
- Reglas de aplicación: no retroactivo (aplica a contraseñas nuevas), fallback a defaults ante setting ausente, recurso versionado con `If-Match` como todo `SystemSetting`, y cada cambio queda registrado en auditoría.

#### PD-014 — Dominios de correo interno (D-16)
- **Decisión:** allowlist de dominios configurada en `SystemSetting` (SUPERUSER editable con `If-Match`), validada al crear/editar el correo de un usuario (el login no re-valida: solo busca la cuenta). Valores iniciales del seed: `organizacion` (el dominio de la cuenta `superuser@organizacion` del PD-006); los dominios reales de la organización se cargan en la primera configuración.
- **Alternativa descartada:** lista fija en código (requiere versión nueva ante cambios de dominio).

#### PD-015 — Invalidación de otras sesiones al cambiar la contraseña propia
- **Decisión:** al cambiar la contraseña propia se invalidan (se eliminan) todas las demás sesiones del `user_id`, manteniendo solo la sesión actual. Elimina el riesgo residual registrado como R-002. Coste UX: las otras pestañas piden re-login.

#### PD-016 — Edición de `username`
- **Decisión:** `username` **inmutable**, igual que el correo. Se deriva del correo institucional (parte local del `email`): en el alta se resuelven juntos, sin tipeo libre del username, y así se evitan errores de tipeo. Corrección ante error: baja del usuario (sin asignaciones) + alta correcta. Elimina el riesgo residual registrado como R-003.
- **Alternativa descartada:** renombre de username por SUPERUSER.

### F2-I5 — Seguridad, logs y comprobantes

#### PD-017 — Retención de logs de seguridad
- **Decisión:** retención completa sin purga en v1, alineada con D-22 ("en principio se retiene todo"). El volumen a escala institucional es bajo y PostgreSQL lo absorbe.
- **Nota registrada:** los `SecurityEvent` contienen datos personales (usuario/IP/fecha); revisar política de purga cuando el volumen de datos personales lo justifique o surja requisito normativo.
- **Alternativa descartada:** purga automática a 12 meses (plazo sin referencias externas y perdería capacidad de auditoría prolongada).

#### PD-018 — Tamaño máximo de comprobantes
- **Decisión:** máximo **10 MB por archivo** validado server-side al subir (streamed, antes de grabar), error `TICKET_RECEIPT_TOO_LARGE` (código aprobado en `docs/specification/api-contracts.md`). El legacy no validaba tamaño. Valor configurable por SUPERUSER en `SystemSetting` (mismo patrón que PD-013).

#### PD-019 — Almacenamiento y backups de comprobantes
- **Decisión:** directorio del servidor/volumen dedicado fuera de la raíz web (coherente con security-design §16: nombres UUID, descarga autenticada), metadatos `TicketReceipt` en BD. Backups: rutina operativa que respalda BD + directorio juntos con restore homologado (documentado en el runbook de F2-I6).
- **Justificación:** BD y dumps no crecen con binarios; permite migrar a object storage en el futuro sin migraciones masivas.
- **Alternativa descartada:** `bytea` en PostgreSQL (backup transaccional único, pero dumps pesados y migración posterior cara).

## Estado del registro

- 21/21 definiciones aprobadas.
- F2-I1 está cerrado; la Fase 2 continúa sin implementación iniciada.
- Riesgos cerrados por estas decisiones: R-002 (PD-015) y R-003 (PD-016). Los riesgos R-001, R-004 y R-005 permanecen.
- Las decisiones con límite posterior a F2-I1 se implementan en los incrementos indicados; la aprobación aquí cubre el diseño completo.
