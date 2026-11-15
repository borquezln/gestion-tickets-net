# Decisiones arquitectónicas — Sistema de Gestión de Tickets

> **Incremento F2-I1 — Decisiones arquitectónicas.**
> **Estado: cerrado.**
> Documento de arquitectura de alto nivel. No implementa código ni crea proyectos.  
> La documentación funcional de Fase 1 está finalizada y se utiliza como fuente funcional de verdad. Este incremento no la modifica.

## 1. Objetivo y alcance

### 1.1 Objetivo

Definir y documentar las decisiones arquitectónicas iniciales del nuevo Sistema de Gestión de Tickets, de modo que la Fase 2 pueda iniciarse con un marco técnico coherente, proporcional a la complejidad del sistema y alineado con la especificación funcional disponible.

### 1.2 Alcance

Este documento cubre stack, arquitectura general, separación frontend/backend, responsabilidades, comunicación, API, persistencia, principios, ADR, registro de decisiones técnicas y transición.

### 1.3 Fuera de alcance

No se crean proyectos, dependencias, esquemas, migraciones, endpoints ni implementaciones. No se modifica la especificación funcional. No se elimina `/legacy` ni documentación de análisis.

### 1.4 Fuentes y precedencia

| Prioridad | Fuente | Uso |
|---|---|---|
| 1 | `docs/definitions/architecture-definitions.md` | Decisiones técnicas de Fase 2; prevalecen sobre las fases anteriores. |
| 2 | `docs/definitions/specification-definitions.md` | Decisiones funcionales de Fase 1; prevalecen sobre Fase 0. |
| 3 | `docs/specification/*` | Especificación funcional y de dominio de Fase 1. |
| 4 | `docs/definitions/analysis-definitions.md` | Decisiones de Fase 0. |
| 5 | `docs/analysis/*` y `/legacy` | Fuentes históricas y de consulta. |

Si una contradicción no puede resolverse aplicando este orden, se documenta y se solicita intervención. La documentación funcional de Fase 1 está finalizada; las decisiones de Fase 2 sólo la complementan o prevalecen cuando lo establecen expresamente.

## 2. Stack tecnológico aprobado

| Componente | Tecnología | Estado |
|---|---|---|
| Framework | .NET 10 LTS | [A] Aprobado |
| Backend | ASP.NET Core | [A] Aprobado |
| Persistencia | Entity Framework Core | [A] Aprobado |
| Base de datos | PostgreSQL | [A] Aprobado |
| Frontend | React + TypeScript | [A] Aprobado |
| Editor | Visual Studio Code | [A] Aprobado |
| Asistente | OpenCode | [A] Aprobado |

No se reabre la selección salvo incompatibilidad técnica concreta.

## 3. Arquitectura general

### 3.1 Estilo

Arquitectura cliente-servidor con API HTTP/JSON, separación física entre frontend y backend, y persistencia relacional. El backend es la única autoridad de negocio, seguridad y datos.

```text
Frontend React (SPA independiente)
        │ HTTPS / JSON + cookie HttpOnly
        ▼
ASP.NET Core Web API (autoridad de negocio y seguridad)
        │
        ▼
PostgreSQL (EF Core)
```

### 3.2 Principios rectores

1. El backend es la autoridad.  
2. Deny-by-default.  
3. Separación de responsabilidades.  
4. Proporcionalidad.  
5. API como contrato.  
6. Persistencia versionada.  
7. Seguridad por diseño.  
8. Trazabilidad.  
9. Sin compatibilidad histórica forzada.

## 4. Separación frontend/backend

### 4.1 Frontend

Renderizado, UX, validaciones de formato/usabilidad, consumo de API, estado de UI, manejo de sesión expirada. No es autoridad de negocio.

### 4.2 Backend

Autenticación, sesiones, autorización, reglas de negocio, persistencia, auditoría, logs de seguridad, archivos protegidos, configuración, contrato de API.

### 4.3 Comunicación

HTTPS, JSON, base `/api/v1`, cookie de sesión `HttpOnly`/`Secure`/`SameSite`, CORS restringido, concurrencia por ETag/`If-Match`, idempotencia opcional, errores consistentes, fechas ISO 8601.

## 5. Responsabilidades de la API

Exponer recursos, aplicar autorización por operación, validar entradas y dominio, gestionar concurrencia/idempotencia, servir comprobantes por endpoint autenticado, auditar cambios y registrar eventos de seguridad. No expone logs de seguridad por `/audit-events` (D-39).

## 6. Persistencia

PostgreSQL + EF Core, UUID subrogados, identificadores funcionales únicos, UTC en base de datos, zona de negocio aplicada en aplicación/presentación, historial inmutable, auditoría y seguridad separadas, archivos con metadatos/checksum, sin migración de datos históricos y seed con `SUPERUSER` inicial con contraseña provisional de un solo uso.

## 7. Principios de diseño

Dominio rico, autorización centralizada, validación en servidor, concurrencia explícita, auditoría por diseño, configuración tipada, pruebas como ciudadanos de primera clase, sin deuda del legado.

## 8. Decisiones aprobadas (ADR)

### ADR-001 — Stack tecnológico aprobado

- **Estado:** [A] Aprobado.  
- **Decisión:** Adoptar el stack aprobado.  
- **Consecuencias:** toda implementación se basa en estas tecnologías.

### ADR-002 — Separación frontend/backend independientes

- **Estado:** [A] Aprobado.  
- **Decisión:** aplicaciones independientes; comunicación por HTTP/JSON.

### ADR-003 — API REST resource-oriented `/api/v1`

- **Estado:** [A] Aprobado.  
- **Decisión:** REST resource-oriented con convenciones de Fase 1.

### ADR-004 — Autenticación por cookie HttpOnly y sesión de servidor

- **Estado:** [A] Aprobado (D-07).  
- **Decisión:** no JWT en navegador ni tokens en `localStorage`.

### ADR-005 — Autorización deny-by-default en backend

- **Estado:** [A] Aprobado (Q-002, D-05, D-31).  
- **Decisión:** toda operación requiere autenticación y autorización explícitas. El mecanismo técnico que garantiza la declaración obligatoria de permisos/alcance se define en ADR-015.

### ADR-006 — Persistencia EF Core + PostgreSQL + migraciones

- **Estado:** [A] Aprobado.  
- **Decisión:** EF Core sobre PostgreSQL, migraciones versionadas.

### ADR-007 — Arquitectura en capas proporcional

- **Estado:** [A] Aprobado.  
- **Contexto:** el modelo de dominio documentado incluye los agregados `User` (con `UserAreaMembership`), `Direction` (con `Area`), `Ticket` (con `TicketClosure`, `TicketReceipt`, `TicketTimelineEntry`), además de `Role`, `Permission`, `RolePermission`, `AuditEntry`, `SecurityEvent` y `SystemSetting`. Existen catálogos (`Role`, `Permission`, tipos y estados de ticket) y objetos de valor (`TicketReopen`, `TicketDeletion`, `TicketAssignment`). Las relaciones incluyen membresías múltiples por usuario, cierres históricos, historial inmutable y auditoría separada de seguridad.  
- **Decisión:** cuatro proyectos: `Api`, `Application`, `Domain`, `Infrastructure`.  
- **Consecuencias:** dependencias hacia el dominio; sin proyectos adicionales sin responsabilidad concreta.

### ADR-008 — Auditoría y logs de seguridad separados

- **Estado:** [A] Aprobado (D-22, D-39).

### ADR-009 — Comprobantes por endpoint autenticado

- **Estado:** [A] Aprobado (Q-019, D-11, D-25, D-30).

### ADR-010 — UTC + zona de negocio configurable

- **Estado:** [A] Aprobado (D-14, D-37).

### ADR-011 — Concurrencia optimista con ETag/If-Match

- **Estado:** [A] Aprobado (D-27).  
- **Decisión técnica coherente:**  
  - Recursos versionados: `User`, `Direction`, `Area`, `Ticket` y `SystemSetting`.  
  - Las operaciones de escritura sobre recursos versionados requieren `If-Match`.  
  - El ETag se genera a partir de `version` (ETag fuerte, por ejemplo `"v{version}"`) y se devuelve en respuestas de lectura/escritura.  
  - Si `If-Match` falta, la operación se rechaza (`428 Precondition Required`).  
  - Si la versión está desactualizada, se responde `409` con código `VERSION_CONFLICT` (`TICKET_VERSION_CONFLICT` para tickets).  
  - El campo `version` en el cuerpo se mantiene como salida/lectura; en Fase 2 no se usará como entrada de concurrencia para evitar duplicar mecanismos.  
- **Consecuencias:** el frontend envía `If-Match`; los conflictos se manejan explícitamente.

### ADR-012 — Sin migración de datos históricos

- **Estado:** [A] Aprobado (Q-021, D-28).

### ADR-013 — Estructura con tres directorios principales

- **Estado:** [A] Aprobado. `/legacy` es transitoria y excepcional, no una cuarta carpeta principal permanente.

### ADR-014 — Eliminación lógica separada del ciclo de vida

- **Estado:** [A] Aprobado (D-01).

### ADR-015 — Autorización deny-by-default con metadatos obligatorios

- **Estado:** [A] Aprobada (PD-020).
- **Contexto:** una política de fallback que sólo exige autenticación no garantiza que cada endpoint declare permiso y alcance.  
- **Decisión:**  
  - Cada endpoint protegido declara explícitamente el permiso requerido mediante un marcador específico, por ejemplo `[Authorize(Policy = "Permission:P-TICKET_VIEW")]` o `.RequireAuthorization("Permission:P-TICKET_VIEW")`.  
  - La política de fallback exige usuario autenticado **y** la presencia de un `EndpointPermissionRequirement` (o equivalente) con el código de permiso correspondiente; una política genérica que sólo requiera usuario autenticado no satisface este requisito y provoca denegación.  
  - Si un endpoint protegido no declara permiso explícito, la autorización falla aunque el usuario esté autenticado.  
  - Los endpoints públicos se declaran explícitamente como anónimos (`[AllowAnonymous]`/`AllowAnonymous()`).  
  - El alcance se valida en la capa de aplicación además de la API.  
  - Se incluyen pruebas automatizadas que enumeran endpoints y verifican metadatos de autorización.  
- **Consecuencias:** no existen endpoints protegidos sin declaración de autorización; la ausencia de metadatos provoca denegación.

### ADR-016 — Estrategia CSRF definida

- **Estado:** [A] Aprobada (PD-003).
- **Decisión:** ver `security-design.md` §10 (SameSite=Lax, validación de `Origin` sin confiar en `Host`, token anti-CSRF en `X-CSRF-TOKEN`, endpoint anónimo/pre-sesión, protección de login contra login-CSRF, tratamiento multipart y respuestas de fallo).

### ADR-017 — Topología de desarrollo y cookie host-only

- **Estado:** [A] Aprobada (PD-005).
- **Decisión:** ver `security-design.md` §12 y `development-environment.md` §9 (frontend/backend HTTPS, CORS exacto, cookie host-only sin `Domain`).

### ADR-018 — Almacenamiento y revocación de sesiones

- **Estado:** [A] Aprobada (PD-001).
- **Decisión:** ver `security-design.md` §4.4 y §12 (ITicketStore sobre PostgreSQL, tabla `user_sessions`, expiración deslizante, revocación, `membership_version`).

### ADR-019 — Bootstrap y activación inicial del SUPERUSER

- **Estado:** [A] Aprobada (PD-006; extiende D-28).  
- **Análisis:** ver `security-design.md` §6.3.
- **Decisión:** seed organizacional mínimo (Dirección + Área mínimas y membresía activa) con cuenta bootstrap `superuser@organizacion` permanente; la excepción de membresía para SUPERUSER quedó descartada. Ver `docs/definitions/architecture-definitions.md` (PD-006).

### ADR-P-002 — Algoritmo de hash de contraseñas

- **Estado:** [A] Aprobada (PD-002).  
- **Contexto:** la Fase 1 exige hash seguro pero no define el algoritmo ni sus parámetros.  
- **Decisión:** `PasswordHasher` de ASP.NET Core (PBKDF2-HMAC-SHA256) con `IterationCount` de ~600 000 (guía OWASP). La alternativa Argon2id (biblioteca third-party) queda **descartada**; el riesgo restante se mitiga con lockout, rate limiting y política de contraseña.  
- **Consecuencias:** el formato V3 del hash permite rehash-on-login ante cambios futuros de parámetros; se implementa en F2-I4. Ver `docs/definitions/architecture-definitions.md` (PD-002).

## 9. Registro de decisiones técnicas

El registro centralizado y asignado por incremento está en `docs/architecture/pending-decisions.md`; las resoluciones aprobadas del usuario están en `docs/definitions/architecture-definitions.md`.  
Este documento no mantiene una lista paralela para evitar divergencias.

## 10. Transición y limpieza

- `/legacy` y `docs/analysis/` son temporales y se conservan por consulta.  
- Necesarios aún para trazabilidad y comprensión del legado.  
- Eliminables en una limpieza controlada cuando su contenido relevante esté preservado.  
- No se eliminan en este incremento.

## 11. Trazabilidad

Ver sección de trazabilidad de `security-design.md` y `pending-decisions.md`. Las ADR [A] se respaldan en D-xx/Q-xx y documentos de Fase 1. Las ADR [P]/[?] son propuestas o decisiones pendientes y no se presentan como aprobadas por el usuario.
