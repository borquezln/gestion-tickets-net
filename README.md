# Gestión de Tickets — `gestion-tickets-net`

Reconstrucción del sistema de gestión de tickets y requerimientos de mantenimiento de **gestionDOA** (PHP puro + MySQL), ahora como **ASP.NET Core Web API (C#)** con un frontend **React** independiente.

> **Estado:** Fase 0 y Fase 1 finalizadas. Fase 2 está en curso. La estructura backend ASP.NET Core y el esqueleto frontend (F2-I2) ya están implementados: la solución compila, la API arranca y el frontend ejecuta sus pruebas. La persistencia y la autenticación todavía no comenzaron. El repositorio conserva la documentación aprobada y el sistema legado como referencia.

## Índice

- [Qué es el sistema](#qué-es-el-sistema)
- [Alcance funcional](#alcance-funcional)
- [Arquitectura objetivo](#arquitectura-objetivo)
- [Estructura del repositorio](#estructura-del-repositorio)
- [Backend: compilación y arranque](#backend-compilación-y-arranque)
- [Frontend: instalación y comandos](#frontend-instalación-y-comandos)
- [Documentación](#documentación)
- [Decisiones funcionales clave](#decisiones-funcionales-clave)
- [Reglas de trabajo](#reglas-de-trabajo)
- [Estado de la Fase 2](#estado-de-la-fase-2)

## Qué es el sistema

El sistema gestiona tickets de un área operativa que resuelve problemas a toda la organización: puede tratarse de sistemas, mantenimiento u otra área. Está pensado para poder ampliarse a varias áreas operativas, con las tareas separadas según la pertenencia de cada usuario.

Cada ticket (llamado también *tarea* o *requerimiento*) registra un problema, opcionalmente para una persona afectada, se asocia a un **área** responsable y a una **dirección/entidad**, y recorre un ciclo de vida de estados hasta su resolución, cancelación o eliminación. El sistema también administra usuarios, roles y catálogos, y ofrece estadísticas por período y por área.

## Alcance funcional

Módulos previstos, tomando como base el sistema legado y las definiciones aprobadas:

| Módulo | Contenido |
|---|---|
| Autenticación y sesión | Login por usuario o correo, sesión con expiración por inactividad, logout y autogestión del perfil. |
| Panel de control | Totales de tareas por estado, usuarios y direcciones; accesos rápidos a los listados. |
| Tickets / requerimientos | Alta, listado, detalle, edición, asignación a agente, cambios de estado, eliminación lógica y restauración. |
| Catálogos | Áreas (dependientes de una dirección) y direcciones/entidades con baja lógica. |
| Usuarios y roles | Alta, baja/reactivación, blanqueo de contraseña, cambio de rol y perfil propio. |
| Estadísticas | Tareas ingresadas, completadas, canceladas y eliminadas; totales, por fecha y por rango. |
| Comprobantes | Un archivo por tarea, validado y servido mediante endpoint autenticado. |
| Auditoría | Historial de estados y trazabilidad de cambios (funcionalidad nueva). |

### Ciclo de vida de un ticket

```
                    iniciar                    terminar
   [Pendiente] ───────────────► [En Progreso] ───────────────► [Completo]
        │                             │
        │ cancelar                    │ cancelar
        ▼                             ▼
   [Cancelado] ◄─────────────────────┘

   Cualquier estado ──eliminar──► [Eliminado] ──restaurar──► (estado previo)
```

Toda tarea nace en **Pendiente**. La eliminación es lógica y conserva el estado anterior para permitir su restauración. Las transiciones de estado se validan en el dominio, no sólo en la interfaz.

## Arquitectura objetivo

```
┌──────────────────┐        HTTP/JSON        ┌──────────────────────────┐
│  Frontend React  │ ──────────────────────► │  ASP.NET Core Web API    │
│ (app independ.)  │ ◄────────────────────── │  (C#)                    │
└──────────────────┘                         └────────────┬─────────────┘
                                                          │
                                                          ▼
                                                 ┌──────────────────┐
                                                 │  Base de datos   │
                                                 └──────────────────┘
```

- **Backend:** ASP.NET Core Web API con C#. Aplica autorización por rol en todos los endpoints, valida las reglas de negocio y expone el contrato JSON que consume el frontend.
- **Frontend:** aplicación React independiente que consume la API; no forma parte del backend.
- **Persistencia:** PostgreSQL. El esquema definitivo es trabajo futuro de Fase 2. El sistema legado utiliza MySQL/MariaDB.
- **Sistema legado:** PHP puro, sin framework ni API. Se conserva íntegramente en `/legacy` como referencia funcional y técnica; no forma parte del build ni se modifica.

## Estructura del repositorio

```
gestion-tickets-net/
├── README.md                  # Este documento
├── .gitignore                 # Ignora bin/, obj/, secretos locales y artefactos
├── docs/
│   ├── definitions/           # Definiciones de Fase 0, Fase 1 y Fase 2
│   ├── specification/         # Fase 1: especificación funcional aprobada
│   ├── architecture/          # Fase 2: decisiones arquitectónicas cerradas
│   └── analysis/              # Fase 0: análisis del sistema legado
│       ├── README.md          # Índice de la documentación de análisis
│       ├── business-overview.md
│       ├── actors-and-permissions.md
│       ├── workflows.md
│       ├── domain-model.md
│       ├── current-functionalities.md
│       ├── business-rules.md
│       ├── legacy-architecture.md
│       └── open-questions.md
├── backend/                   # Solución ASP.NET Core
│   ├── GestionTickets.sln
│   ├── global.json
│   ├── Directory.Build.props
│   ├── src/
│   │   ├── GestionTickets.Api/
│   │   ├── GestionTickets.Application/
│   │   ├── GestionTickets.Domain/
│   │   └── GestionTickets.Infrastructure/
│   └── tests/
│       └── GestionTickets.Api.Tests/
├── legacy/                    # Sistema PHP original (sólo referencia, no modificar)
└── frontend/                  # Esqueleto React + TypeScript + Vitest
    ├── .nvmrc
    ├── package.json
    ├── pnpm-lock.yaml
    ├── tsconfig*.json
    ├── vite.config.ts
    └── src/
        ├── app/
        ├── features/
        ├── components/
        ├── services/
        ├── hooks/
        ├── types/
        ├── styles/
        └── test/
```

### Backend: compilación y arranque

Requisitos: .NET SDK 10.0.403 o un parche posterior de la misma banda (el `global.json` usa `latestPatch`).

```bash
cd backend
dotnet restore GestionTickets.sln
dotnet build GestionTickets.sln
dotnet test GestionTickets.sln
dotnet run --project src/GestionTickets.Api --launch-profile https
```

La API escucha en `https://localhost:5001`. Es un esqueleto de composición: todavía no expone endpoints de negocio. En desarrollo, el certificado debe confiarse con `dotnet dev-certs https --trust`.

No hay conexión a PostgreSQL, entidades, migraciones, autenticación ni autorización en este estado. Esos temas pertenecen a incrementos posteriores de Fase 2.

### Frontend: instalación y comandos

Requisitos: Node.js 24 LTS o superior (declarado en `frontend/.nvmrc` y `engines`) y pnpm vía `corepack enable`.

```bash
cd frontend
pnpm install --frozen-lockfile
pnpm build
pnpm test
pnpm test:coverage
pnpm dev
```

El servidor de desarrollo escucha en `https://localhost:5173` con certificado autofirmado (`@vitejs/plugin-basic-ssl`). El frontend es un esqueleto: no incluye funcionalidad de negocio, cliente HTTP ni archivos `.env*`; esos elementos pertenecen a un incremento posterior, todavía sin asignación.

## Documentación

Toda la documentación está en español y se complementa entre sí:

| Documento | Contenido |
|---|---|
| [`docs/definitions/analysis-definitions.md`](./docs/definitions/analysis-definitions.md) | Decisiones tomadas en Fase 0. |
| [`docs/definitions/specification-definitions.md`](./docs/definitions/specification-definitions.md) | Decisiones tomadas en Fase 1 (D-01 a D-39); prevalecen sobre las de Fase 0. |
| [`docs/definitions/architecture-definitions.md`](./docs/definitions/architecture-definitions.md) | Decisiones tomadas en Fase 2 (PD-001 a PD-021); prevalecen sobre las de Fase 0 y Fase 1. |
| [`docs/specification/`](./docs/specification/) | Especificación funcional aprobada de Fase 1. |
| [`docs/architecture/`](./docs/architecture/) | Decisiones arquitectónicas, seguridad, estructura, entorno y registro técnico de Fase 2. |
| [`docs/analysis/README.md`](./docs/analysis/README.md) | Índice y guía de lectura de la Fase 0. |
| [`business-overview.md`](./docs/analysis/business-overview.md) | Contexto de negocio, módulos, entidades y alcance real. |
| [`actors-and-permissions.md`](./docs/analysis/actors-and-permissions.md) | Actores, roles y matriz de permisos del sistema anterior. |
| [`workflows.md`](./docs/analysis/workflows.md) | Flujos funcionales paso a paso, con validaciones y desviaciones. |
| [`domain-model.md`](./docs/analysis/domain-model.md) | Entidades, relaciones, integridad y ambigüedades del modelo. |
| [`current-functionalities.md`](./docs/analysis/current-functionalities.md) | Inventario de funcionalidades activas, parciales, sólo endpoint y huérfanas. |
| [`business-rules.md`](./docs/analysis/business-rules.md) | Catálogo de 54 reglas de negocio con evidencia y nivel de certeza. |
| [`legacy-architecture.md`](./docs/analysis/legacy-architecture.md) | Arquitectura, deuda técnica, riesgos de migración y qué no trasladar. |
| [`open-questions.md`](./docs/analysis/open-questions.md) | Preguntas abiertas originales del análisis (resueltas en `docs/definitions/specification-definitions.md`). |

### Orden de lectura recomendado

1. `docs/definitions/analysis-definitions.md` — definiciones funcionales originales.
2. `docs/definitions/specification-definitions.md` — decisiones aprobadas de Fase 1.
3. `docs/analysis/business-overview.md` — qué hacía el sistema anterior y por qué.
4. `docs/analysis/business-rules.md` + `domain-model.md` + `workflows.md` — comportamiento y datos a preservar o corregir.
5. `docs/analysis/legacy-architecture.md` — qué no repetir en la nueva arquitectura.
6. `docs/analysis/open-questions.md` — contexto de las decisiones ya tomadas.

## Decisiones funcionales clave

Resumen de las definiciones aprobadas que condicionan el diseño:

| Tema | Decisión | Ref. |
|---|---|---|
| Autorización | Debe aplicarse por rol en el backend, en todos los endpoints. | Q-002 |
| Baja de usuarios | Impide el login e invalida sesiones activas y/o la contraseña. | Q-001 |
| Roles | Administrador y Administrativo, con previsión de Agente y Responsable de área; los índices pueden reiniciarse. | Q-003 |
| Administración | Sólo el Administrador gestiona áreas, direcciones y usuarios. | Q-004 |
| Visibilidad de tareas | Por rol, a definir junto con los roles nuevos. | Q-005 |
| Identidad | Nombre de usuario y correo únicos, con constraints en base de datos. | Q-013 |
| Vínculo con el creador | Por legajo, no por nombre; las tareas sobreviven a bajas y renombres. | Q-014 |
| Asignación | Asignar tareas a un agente es funcionalidad nueva, no una migración. | Q-015 |
| Eliminación | Permitida desde cualquier estado, con restauración y conservación del estado previo. | Q-016 |
| Trazabilidad | Historial de estados y auditoría de cambios como funcionalidad nueva. | Q-017 |
| Comprobantes | Un archivo por tarea, validado por tipo/tamaño, con nombre identificable y servido por endpoint autenticado. | Q-019 |
| Nota electrónica | Se persiste el identificador del documento gestionado en otro sistema. | Q-007 |
| Direcciones | PK subrogada, código editable y único, y baja lógica para preservar históricos. | Q-018 |
| Estados | Las transiciones se validan en el dominio. | Q-008 |
| Estadísticas | Completadas por fecha de solución e ingresadas por fecha de creación; incluyen eliminadas y canceladas. | Q-020 |
| Fechas | `fechaProblema` es la fecha declarada y editable del problema; `fechaCreada` es la marca temporal del registro. | Q-023 |
| Zona horaria | Zona local fija para todo el sistema. | Q-026 |
| Datos históricos | No se migran: el nuevo sistema se inicializa de cero. | Q-021 |

## Reglas de trabajo

- **Las decisiones se aplican por fase.** `docs/definitions/analysis-definitions.md` materializa las de Fase 0; `docs/definitions/specification-definitions.md` prevalece sobre ellas para Fase 1; y `docs/definitions/architecture-definitions.md` prevalece sobre ambas para Fase 2.
- **`legacy/` es intocable.** Se conserva como referencia; no se corrige, no se refactoriza y no se incluye en el build.
- **`docs/analysis/` describe el sistema anterior**, no el destino. No debe interpretarse como especificación del nuevo sistema.
- **No se migran datos históricos.** Las decisiones de modelo se toman para el nuevo sistema, no para compatibilizar el anterior.
- **La seguridad no es cosmética:** autorización, validación de estado y validación de archivos se aplican en el servidor.
- **Sin secretos en el repositorio:** la configuración sensible se maneja por variables de entorno o User Secrets.
- Los cambios que alteren comportamiento funcional deben reflejarse primero en `docs/definitions/specification-definitions.md` y en `docs/specification/`.

## Estado de la Fase 2

Fase 2 está en curso. Las decisiones arquitectónicas, el stack definitivo y el registro de decisiones técnicas están cerrados; persisten los riesgos operativos declarados, que no bloquean los pasos siguientes.

F2-I2 (estructura de la solución ASP.NET Core, configuración y esqueleto frontend) está implementado: existen los cuatro proyectos backend, las referencias dirigidas al dominio, `global.json`, la configuración tipada de CORS, el arranque HTTPS y las pruebas de compilación/arranque. También existe el esqueleto frontend con Vite, React, TypeScript y Vitest + React Testing Library + jsdom + coverage V8 (PD-010). Quedan pendientes la persistencia con PostgreSQL y EF Core, las entidades y migraciones, la autenticación y las sesiones, la autorización y la seguridad, la integración del frontend con la API y el entorno de desarrollo completo.

---

- Sistema legado: [`legacy/README.md`](./legacy/README.md)
- Análisis completo: [`docs/analysis/README.md`](./docs/analysis/README.md)
- Definiciones aprobadas: [`docs/definitions/specification-definitions.md`](./docs/definitions/specification-definitions.md)
