# Estructura de la solución — Sistema de Gestión de Tickets

> **Incremento F2-I2 — Estructura de solución ASP.NET Core y configuración.**
> Estructura prevista; no crea proyectos ni código ni supone el inicio del incremento.
> `/legacy` es una carpeta transitoria y excepcional en la raíz, no una cuarta carpeta principal permanente.

## 1. Principios

1. Tres directorios principales: `docs/`, `backend/`, `frontend/`.  
2. Proporcionalidad.  
3. Separación de responsabilidades.  
4. Dependencias dirigidas hacia el dominio.  
5. Transición controlada.

## 2. Árbol propuesto

```text
gestion-tickets-net/
├── README.md
├── .gitignore
├── docs/
├── backend/
│   ├── GestionTickets.sln
│   ├── global.json
│   ├── .config/dotnet-tools.json
│   ├── src/
│   │   ├── GestionTickets.Api/
│   │   ├── GestionTickets.Application/
│   │   ├── GestionTickets.Domain/
│   │   └── GestionTickets.Infrastructure/
│   └── tests/
├── frontend/
└── legacy/   # transitoria
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

| Proyecto | Responsabilidad | Dependencias |
|---|---|---|
| `Domain` | Entidades, enums, invariantes, reglas puras. | Ninguna |
| `Application` | Casos de uso, servicios, DTOs de aplicación, interfaces, autorización, validaciones. | `Domain` |
| `Infrastructure` | EF Core, DbContext, configuraciones, migraciones, repositorios, archivos, settings. | `Domain`, `Application` |
| `Api` | Composición, endpoints, middleware, auth, errores, CORS/CSRF. | `Domain`, `Application`, `Infrastructure` |

### 4.2 Estructura interna

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
│   ├── GestionTicketsDbContext.cs
│   ├── Configurations/
│   ├── Migrations/
│   └── Repositories/
├── Files/
├── Security/
├── Time/
└── DependencyInjection/

backend/src/GestionTickets.Api/
├── Contracts/
├── Endpoints/ (o Controllers/)
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

`backend/src/GestionTickets.Infrastructure/Persistence/Migrations/`. Generación y aplicación mediante `dotnet-ef` versionado (ver `development-environment.md`).

### 4.5 Configuración

`appsettings*.json` + User Secrets/variables de entorno. Sin secretos versionados.

## 5. Frontend

### 5.1 Estructura

```text
frontend/
├── package.json
├── pnpm-lock.yaml
├── tsconfig.json
├── vite.config.ts
├── public/
└── src/
    ├── app/
    ├── features/
    ├── components/
    ├── services/
    ├── hooks/
    ├── types/
    └── styles/
```

### 5.2 Cliente HTTP

El cliente HTTP centraliza el envío de solicitudes con credenciales (`credentials: 'include'`) y el tratamiento de respuestas (401, 403, 409, renovación de actividad implícita del backend).  
No administra la cookie `HttpOnly`; esa responsabilidad corresponde al navegador.  
La “renovación” observable corresponde a la expiración deslizante de 60 minutos gestionada por el backend ante actividad autenticada, no a refresh tokens.

### 5.3 Organización

Organización por feature (vertical slicing), con componentes compartidos sólo cuando se reutilizan. Tipos alineados al contrato de API.

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
| Configuración backend | `Api/appsettings*.json` + User Secrets/env |
| Configuración frontend | `frontend/.env*` (sin secretos) |
| Migraciones | `Infrastructure/Persistence/Migrations/` |
| Pruebas backend | `backend/tests/...` |
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
