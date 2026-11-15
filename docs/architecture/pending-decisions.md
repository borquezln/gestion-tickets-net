# Registro de decisiones técnicas — F2-I1

> Registro centralizado. Evita listas divergentes entre documentos.  
> `[A]` aprobado, `[P]` propuesta técnica, `[?]` pendiente, `[R]` riesgo.  
> **Estado de F2-I1: cerrado.**
> Las definiciones PD-001 a PD-021 están aprobadas. Las resoluciones completas
> (justificación y alternativas descartadas) están en
> [`docs/definitions/architecture-definitions.md`](../definitions/architecture-definitions.md).
> Este archivo queda como registro de estado y apunta a ese documento como resolución de referencia.

## 1. Decisiones cerradas (aprobadas por el usuario)

| ID | Descripción | Estado | Impacto | Incremento límite |
|---|---|---|---|---|
| PD-001 | Almacén de sesiones: PostgreSQL (`ITicketStore`) vs caché distribuida | [A] | Medio | F2-I1 → PostgreSQL (`user_sessions`, ADR-018) |
| PD-002 | Algoritmo de hash: PBKDF2 vs Argon2id | [A] | Medio | F2-I4 → PBKDF2 (`PasswordHasher`, ~600.000 iteraciones) |
| PD-003 | Mecanismo CSRF: SameSite + Origin + token | [A] | Alto | F2-I1 → ratifica ADR-016 |
| PD-004 | Persistencia de claves Data Protection | [A] | Medio | F2-I1 → PostgreSQL (provider EF Core) |
| PD-005 | Topología dev HTTPS y certificados | [A] | Medio | F2-I1 → ratifica ADR-017 (HTTPS en dev) |
| PD-006 | Bootstrap SUPERUSER: seed organizacional mínimo vs excepción de membresía | [A] | Alto | F2-I1 → seed mínimo, cuenta `superuser@organizacion` permanente |
| PD-007 | Activación inicial: primera contraseña y canal de entrega | [A] | Medio | F2-I4 → contraseña provisional de un solo uso (12 caracteres, 72 h) |
| PD-008 | Versión Node.js LTS | [A] | Bajo | F2-I1 → Node.js 24 LTS o superior |
| PD-009 | Gestor de paquetes | [A] | Bajo | F2-I1 → pnpm vía `corepack` |
| PD-010 | Framework de pruebas frontend | [A] | Bajo | F2-I2 → Vitest + RTL + jsdom |
| PD-011 | Versión PostgreSQL | [A] | Bajo | F2-I1 → PostgreSQL 18.x |

## 2. Decisiones técnicas cerradas

| ID | Descripción | Estado | Impacto | Incremento límite |
|---|---|---|---|---|
| PD-012 | Umbrales de bloqueo y rate limiting | [A] | Medio | F2-I4 → 3 intentos por identificador / 5 por IP, 15 min |
| PD-013 | Política de contraseñas: valores y configuración | [A] | Medio | F2-I4 → defaults NIST, configurable por SUPERUSER |
| PD-014 | Dominios de correo interno | [A] | Medio | F2-I4 → allowlist en `SystemSetting` (seed: `organizacion`) |
| PD-015 | Invalidación de otras sesiones al cambiar contraseña propia | [A] | Bajo/Medio | F2-I4 → se invalidan las demás sesiones |
| PD-016 | Edición de `username` por SUPERUSER | [A] | Bajo | F2-I4 → inmutable, derivado del correo institucional |
| PD-017 | Retención de logs de seguridad | [A] | Medio | F2-I5 → retención completa sin purga en v1 |
| PD-018 | Tamaño máximo de comprobantes | [A] | Bajo | F2-I5 → 10 MB, configurable en `SystemSetting` |
| PD-019 | Almacenamiento y backups de comprobantes | [A] | Medio | F2-I5 → directorio fuera de raíz web + backup BD + carpeta |
| PD-020 | 404 vs 403 por endpoint (ADR-015) | [A] | Bajo | F2-I1 → matriz por visibilidad legítima |
| PD-021 | Versiones exactas de .NET SDK y Node | [A] | Bajo | F2-I1 → pinning (`global.json` / `.nvmrc` / tag Docker) |

## 3. Riesgos

| ID | Descripción | Estado | Tratamiento |
|---|---|---|---|
| R-001 | Sin expiración absoluta de sesión | [R] | Evaluar en Fase 2; no inventar. |
| R-002 | Invalidación de otras sesiones al cambiar contraseña propia | Resuelto | PD-015: se invalidan. |
| R-003 | Edición de `username` | Resuelto | PD-016: inmutable. |
| R-004 | MFA fuera de alcance | [R] | No implementar (D-23). |
| R-005 | Refresh tokens no aprobados | [R] | No implementar. |

## 4. Notas

- Las decisiones [A] de Fase 1 (D-xx/Q-xx) no se modifican aquí.  
- Las 21 definiciones técnicas están aprobadas: no quedan decisiones técnicas [P] ni [?] abiertas; solo persisten los riesgos R-001, R-004 y R-005.
- La implementación de cada decisión se materializa en el incremento indicado en "Incremento límite"; la aprobación aquí cubre el diseño.  
- **F2-I2 implementado (estructura backend, configuración y esqueleto frontend).** PD-010 (Vitest + React Testing Library + jsdom + coverage V8) quedó materializado en F2-I2 según su asignación vigente. No se implementó funcionalidad de negocio ni integración con la API. PostgreSQL, EF Core, entidades, migraciones, autenticación, sesiones y autorización permanecen fuera de F2-I2.
