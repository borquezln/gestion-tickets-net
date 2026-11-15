# Estructura de la solución — Sistema de Gestión de Tickets

> **Incremento F2-I2 — Estructura de solución ASP.NET Core y configuración.**
> **Estado: implementado. Pendiente de revisión y cierre del incremento.**
> La solución backend compila y arranca y el esqueleto frontend compila y ejecuta sus pruebas; este documento refleja la estructura materializada.
> Fuera de este incremento: PostgreSQL, EF Core, entidades, migraciones, autenticación, sesiones, autorización y funcionalidad de negocio.
> `/legacy` es una carpeta transitoria y excepcional en la raíz, no una cuarta carpeta principal permanente.

## 1. Principios

1. Tres directorios principales: `docs/`, `backend/`, `frontend/`.  
2. Proporcionalidad.  
3. Separación de responsabilidades.  
4. Dependencias dirigidas hacia el dominio.  
5. Transición controlada.

## 2. Árbol implementado

```text
gestion-tickets-net/
├── README.md
├── .gitignore
├── docs/
├── backend/
│   ├── GestionTickets.sln
│   ├── global.json
│   ├── Directory.Build.props
│   ├── src/
│   │   ├── GestionTickets.Api/
│   │   │   ├── Configuration/
│   │   │   ├── Contracts/
│   │   │   ├── Endpoints/
│   │   │   ├── Filters/
│   │   │   ├── Mappings/
│   │   │   ├── Middleware/
│   │   │   ├── Properties/launchSettings.json
│   │   │   ├── Program.cs
│   │   │   └── appsettings*.json
│   │   ├── GestionTickets.Application/
│   │   ├── GestionTickets.Domain/
│   │   └── GestionTickets.Infrastructure/
│   └── tests/
│       └── GestionTickets.Api.Tests/
├── frontend/
│   ├── .nvmrc
│   ├── index.html
│   ├── package.json
│   ├── pnpm-lock.yaml
│   ├── tsconfig.json
│   ├── tsconfig.app.json
│   ├── tsconfig.node.json
│   ├── vite.config.ts
│   ├── public/
│   └── src/
│       ├── app/
│       ├── features/
│       ├── components/
│       ├── services/
│       ├── hooks/
│       ├── types/
│       ├── styles/
│       └── test/
└── legacy/     # transitoria
```

## 3. Responsabilidad de directorios principales

| Directorio | Responsabilidad |
|---|---|
| `docs/` | Documentación. |
| `backend/` | Solución ASP.NET Core. |
| `frontend/` | SPA React + TypeScript. |
| `legacy/` | Sistema PHP original, sólo referencia, transitoria. |

## 4. Backend

### 4.1 Proyectos

| Proyecto | Responsabilidad | Dependencias | Estado F2-I2 |
|---|---|---|---|
| `Domain` | Entidades, enums, invariantes, reglas puras. | Ninguna | Proyecto creado, sin código de dominio. |
| `Application` | Casos de uso, servicios, DTOs de aplicación, interfaces, autorización, validaciones. | `Domain` | Proyecto creado, sin casos de uso. |
| `Infrastructure` | EF Core, DbContext, configuraciones, migraciones, repositorios, archivos, settings. | `Domain`, `Application` | Proyecto creado, sin persistencia. |
| `Api` | Composición, endpoints, middleware, auth, errores, CORS/CSRF. | `Domain`, `Application`, `Infrastructure` | Proyecto creado con arranque y CORS. |

Las referencias entre proyectos se materializaron exactamente con la dirección anterior. `Api` referencia a las tres capas; no existe ninguna referencia desde `Domain` hacia otras capas.

### 4.2 Estructura interna

Las carpetas se crearon como estructura de trabajo; las que aún no tienen código contienen un `.gitkeep`. `Directory.Build.props` centraliza `Nullable`, `ImplicitUsings` y `TreatWarningsAsErrors` para todos los proyectos. En la estructura interna de `Api`, `Configuration/` es la única adición respecto de la estructura prevista y aloja la configuración tipada.

```text
backend/src/GestionTickets.Domain/
├── Common/
├── Enums/
├── Entities/
├── ValueObjects/
└── Services/

backend/src/GestionTickets.Application/
├── Abstractions/
├── Authorization/
├── Common/
├── DTOs/
├── Features/
├── Mapping/
└── Validation/

backend/src/GestionTickets.Infrastructure/
├── Persistence/
│   ├── Configurations/
│   ├── Migrations/
│   └── Repositories/
├── Files/
├── Security/
├── Time/
└── DependencyInjection/

backend/src/GestionTickets.Api/
├── Configuration/
├── Contracts/
├── Endpoints/
├── Middleware/
├── Filters/
├── Mappings/
├── Program.cs
└── appsettings*.json
```

### 4.3 DTOs y contratos de transporte

- `Application` define los modelos de entrada/salida de los casos de uso (commands, queries, DTOs de aplicación).  
- `Api` puede reutilizar esos modelos como contrato de transporte cuando no haya necesidad de forma HTTP específica.  
- Si se requieren DTOs propios de transporte, se ubican en `Api/Contracts` y se mapean en `Api/Mappings`.  
- Se evita duplicar contratos: un solo modelo por caso de uso y mapeo explícito sólo para preocupaciones HTTP (ETag, headers, códigos).  
- La definición pública del contrato HTTP sigue en `docs/specification/api-contracts.md`.

### 4.4 Migraciones

`backend/src/GestionTickets.Infrastructure/Persistence/Migrations/`. Generación y aplicación mediante `dotnet-ef` versionado (ver `development-environment.md`). La carpeta y el manifiesto de herramientas se completan en el incremento de persistencia (F2-I3).

### 4.5 Configuración

`appsettings*.json` + User Secrets/variables de entorno. Sin secretos versionados.

Configuración implementada en F2-I2:

- `Cors:AllowedOrigins`: allowlist exacta de orígenes frontend. En `appsettings.json` queda vacía; `appsettings.Development.json` declara `https://localhost:5173`.
- La API valida la allowlist al arrancar: exige al menos un origen, HTTPS, sin comodines, sin ruta, query, fragmento ni barra final. Un valor inválido o ausente impide el arranque.
- El origen configurado se aplica con credenciales habilitadas (`AllowCredentials`), conforme a `security-design.md` §11 y ADR-017.
- No se configuró cadena de conexión ni User Secrets: ambos pertenecen a los incrementos de persistencia y seguridad.

### 4.6 Arranque y composición

`Program.cs` usa el modelo de hosting mínimo de ASP.NET Core y compone:

1. La configuración de la API y su política CORS (`AddApiConfiguration`).
2. La redirección HTTPS.
3. El enrutamiento y la política CORS.

No se registran endpoints, autenticación, autorización, persistencia ni servicios de dominio en este incremento. El tipo `Program` se expone como parcial para las pruebas de integración.

### 4.7 Validaciones del backend

Desde `backend/`:

```bash
dotnet restore GestionTickets.sln
dotnet build GestionTickets.sln
dotnet test GestionTickets.sln
dotnet run --project src/GestionTickets.Api --launch-profile https
```

- `dotnet build` debe finalizar sin advertencias ni errores.
- `dotnet test` ejecuta las pruebas de arranque y configuración de `GestionTickets.Api.Tests`.
- `dotnet run` debe escuchar en `https://localhost:5001`; una ruta inexistente responde `404`.
- Las pruebas verifican que la aplicación arranca, que el origen configurado pasa el preflight CORS con credenciales, que un origen desconocido no recibe cabecera de permiso y que la validación de configuración rechaza orígenes inválidos.

## 5. Frontend

### 5.1 Estructura

```text
frontend/
├── .nvmrc
├── index.html
├── package.json
├── pnpm-lock.yaml
├── tsconfig.json
├── tsconfig.app.json
├── tsconfig.node.json
├── vite.config.ts
├── public/
└── src/
    ├── app/
    │   ├── __tests__/App.test.tsx
    │   ├── App.tsx
    │   └── main.tsx
    ├── features/
    ├── components/
    ├── services/
    ├── hooks/
    ├── types/
    ├── styles/global.css
    ├── test/setup.ts
    └── vite-env.d.ts
```

Materializado en F2-I2:

- Vite + React + TypeScript, entrada `src/app/main.tsx`.
- Vitest + React Testing Library + jsdom y coverage V8 (PD-010), con setup en `src/test/setup.ts`.
- Dev server HTTPS en `https://localhost:5173` mediante `@vitejs/plugin-basic-ssl`, conforme a ADR-017/PD-005; el certificado autofirmado se acepta una vez por desarrollador.
- Node.js declarado en `.nvmrc` (`24`) y `engines` (`>=24.15.0`); pnpm declarado en `packageManager`.
- Sin funcionalidad de negocio, sin cliente HTTP y sin archivos `.env*`; esos elementos pertenecen a incrementos posteriores.

### 5.2 Cliente HTTP

Pendiente de implementación en un incremento posterior, todavía sin asignación en el plan actual. El cliente deberá centralizar el envío de solicitudes con credenciales (`credentials: 'include'`) y el tratamiento de respuestas (401, 403, 409, renovación de actividad implícita del backend).

No administra la cookie `HttpOnly`; esa responsabilidad corresponde al navegador.

La “renovación” observable corresponde a la expiración deslizante de 60 minutos gestionada por el backend ante actividad autenticada, no a refresh tokens.

### 5.3 Organización

Organización por feature (vertical slicing), con componentes compartidos sólo cuando se reutilizan. Tipos alineados al contrato de API.

### 5.4 Validaciones del frontend

Desde `frontend/`:

```bash
pnpm install --frozen-lockfile
pnpm build
pnpm test
pnpm test:coverage
pnpm dev
```

- `pnpm build` ejecuta TypeScript y el build de producción de Vite.
- `pnpm test` ejecuta Vitest con jsdom y React Testing Library.
- `pnpm test:coverage` genera coverage con provider V8.
- `pnpm dev` debe escuchar en `https://localhost:5173`.
- El esqueleto no contiene funcionalidad de negocio ni cliente HTTP; solo el shell de la aplicación y su prueba de humo.

## 6. Dependencias y reglas

- `Domain` no depende de otros proyectos.  
- `Application` depende sólo de `Domain`.  
- `Infrastructure` implementa interfaces de `Application`.  
- `Api` compone y expone.  
- Frontend depende sólo del contrato HTTP.  
- No crear proyectos adicionales sin responsabilidad concreta.  
- No poner lógica de negocio en endpoints ni componentes.

## 7. Ubicación de configuración, pruebas y migraciones

| Elemento | Ubicación |
|---|---|
| Configuración backend | `Api/appsettings*.json` + `Api/Configuration/` + User Secrets/env |
| Configuración frontend | `frontend/.env*` (sin secretos) |
| Migraciones | `Infrastructure/Persistence/Migrations/` |
| Pruebas backend | `backend/tests/GestionTickets.Api.Tests/` y futuros proyectos en `backend/tests/` |
| Pruebas frontend | `frontend/src/**/__tests__/` o `frontend/tests/` |
| Documentación | `docs/` |

## 8. Convenciones de nombres

### 8.1 Backend (C#)

PascalCase tipos/métodos/propiedades públicas; camelCase variables/parámetros; `_camelCase` campos privados; `I` para interfaces; un tipo por archivo; namespaces `GestionTickets.<Capa>.<Area>`; entidades/enums en inglés.

### 8.2 Base de datos (PostgreSQL)

Tablas/columnas `snake_case`; PK `id` (uuid); FK `<entidad>_id`; índices `ix_<tabla>_<columna(s)>`; unicidad `uq_<tabla>_<columna(s)>`.

### 8.3 Frontend (TypeScript/React)

Componentes `PascalCase`; hooks `useX`; funciones/variables `camelCase`; constantes `UPPER_SNAKE_CASE`; archivos de componente `PascalCase.tsx`; utilidades `camelCase.ts`; carpetas `kebab-case`.

### 8.4 API

Recursos en plural minúsculas; campos `snake_case`; códigos de error estables en inglés; mensajes al usuario en español.

## 9. Criterios para incorporar nuevas funcionalidades

1. Modelar regla en `Domain`.  
2. Agregar caso de uso en `Application`.  
3. Actualizar persistencia y migración si cambia el modelo.  
4. Agregar endpoint con permiso y alcance.  
5. Agregar feature frontend.  
6. Registrar auditoría/logs de seguridad.  
7. Agregar pruebas.  
8. Actualizar documentación si cambia contrato/permisos.  
9. Actualizar matriz de permisos/alcance.

No agregar funcionalidades fuera de la especificación sin actualizar primero la documentación de Fase 1.

## 10. Transición

- `/legacy`, `docs/analysis/` y `docs/definitions/analysis-definitions.md` son temporales.  
- Necesarios para trazabilidad y comprensión del legado.  
- Eliminables en limpieza controlada cuando su contenido relevante esté preservado.  
- No se eliminan en este incremento.

## 11. Pendientes inmediatos

| Tema | Incremento previsto |
|---|---|
| PostgreSQL, EF Core, entidades, migraciones y `dotnet-ef` | F2-I3 |
| Autenticación, sesiones, contraseñas y autorización | F2-I4 |
| Seguridad, logs y comprobantes | F2-I5 |
| Integración frontend con la API (cliente HTTP y `.env*`) | Pendiente de asignación |
| Funcionalidades de negocio del frontend | Pendiente de asignación |
| Entorno de desarrollo, pruebas y documentación técnica | F2-I6 |

PD-010 quedó materializado en F2-I2: el frontend existe y ejecuta Vitest + React Testing Library + jsdom + coverage V8. No se implementó funcionalidad de negocio ni integración con la API.
