# Entorno de desarrollo — Sistema de Gestión de Tickets

> **Incremento F2-I6 — Entorno de desarrollo, pruebas y documentación técnica.**
> Entorno previsto, sin credenciales reales; su documentación no supone el inicio del incremento.

## 1. Versiones

| Componente | Versión | Estado |
|---|---|---|
| .NET SDK | 10.0.403 | [A] Aprobado |
| EF Core | 10.x | [A] Aprobado |
| dotnet-ef | 10.x (manifest) | [P] Propuesta |
| PostgreSQL | 18.x | [A] Aprobado (PD-011) |
| Node.js | 24 LTS o superior | [A] Aprobado (PD-008) |
| Gestor de paquetes | pnpm + pnpm-lock.yaml | [A] Aprobado (PD-009) |
| VS Code | Última estable | [A] Aprobado |
| OpenCode | Disponible | [A] Aprobado |

## 2. Reproducibilidad .NET

- `global.json` en `backend/` fijando el SDK: `{ "sdk": { "version": "10.0.403", "allowPrerelease": false } }`.  
- Manifiesto local de herramientas: `backend/.config/dotnet-tools.json` con `dotnet-ef` en versión fijada.  
- Instalación de herramientas: `dotnet tool restore`.

## 3. .NET SDK y herramientas

```bash
dotnet --version
dotnet --list-sdks
dotnet tool install --global dotnet-ef   # o dotnet tool restore
dotnet ef --version
```

Extensiones VS Code: C# Dev Kit, .NET, ESLint, Prettier, GitLens (opcional), REST Client (opcional).

Comandos:

```bash
dotnet restore
dotnet build
dotnet run --project backend/src/GestionTickets.Api
dotnet test
dotnet ef migrations add <Nombre> --project backend/src/GestionTickets.Infrastructure --startup-project backend/src/GestionTickets.Api
dotnet ef database update --project backend/src/GestionTickets.Infrastructure --startup-project backend/src/GestionTickets.Api
```

## 4. PostgreSQL

- Instalar PostgreSQL 18 (versión aprobada, PD-011).  
- Crear usuario y base `gestion_tickets_dev`.  
- No usar `postgres` con contraseña vacía en desarrollo compartido.  
- Conexión: `Host=localhost;Port=5432;Database=gestion_tickets_dev;Username=<usuario_dev>;Password=<secret>` (sin credenciales reales).  
- Herramientas: `psql`, pgAdmin/DBeaver (opcional).

## 5. EF Core y migraciones

- Paquetes NuGet en `Infrastructure` y `Api`.  
- Migraciones versionadas en `Infrastructure/Persistence/Migrations/`.  
- Desarrollo: `dotnet ef database update`.  
- Producción: pipeline controlado (decisión operativa).  
- No editar migraciones aplicadas.

## 6. Node.js y gestor de paquetes

- Instalar Node.js 24 LTS o superior (PD-008).
- Verificar: `node --version`, `pnpm --version`.  
- Usar pnpm con `pnpm-lock.yaml`; se activa vía `corepack enable` (PD-009); instalaciones reproducibles con `pnpm install --frozen-lockfile`.  

Comandos:

```bash
pnpm install --frozen-lockfile
pnpm run dev
pnpm run build
pnpm test
pnpm run lint
```

## 7. VS Code

Extensiones: C# Dev Kit, .NET, ESLint, Prettier, GitLens (opcional), REST Client (opcional).  
Configuración de workspace versionada sin secretos. Depuración backend mediante launch config.

## 8. Configuración local

- Backend: `appsettings.json` (no sensibles) + `appsettings.Development.json` + User Secrets.  
- Frontend: `.env.development` (sólo no sensibles, URL base de API) y `.env.example` sin secretos.  
- Variables de entorno en producción.  
- No se versionan secretos.

User Secrets:

```bash
dotnet user-secrets init --project backend/src/GestionTickets.Api
dotnet user-secrets set "ConnectionStrings:Default" "Host=localhost;Port=5432;Database=gestion_tickets_dev;Username=<usuario>;Password=<secret>" --project backend/src/GestionTickets.Api
```

## 9. Ejecución local y topología segura

- PostgreSQL primero.  
- Backend: `https://localhost:5001`.  
- Frontend: `https://localhost:5173`.  
- CORS permite origen exacto `https://localhost:5173` con credenciales.  
- React usa `credentials: 'include'`.  
- Certificados HTTPS de desarrollo confiados.  
- Cookie `Secure=true`, `SameSite=Lax`, host-only.  
- Esta topología evita debilitar seguridad de producción.

## 10. Pruebas

### 10.1 Backend

- Framework: xUnit.  
- Unitarias de dominio (invariantes, máquina de estados, elegibilidad).  
- Aplicación con repositorios en memoria/mocks.  
- Integración con base de datos de prueba y autenticación.

### 10.2 Frontend

- Framework: Vitest + React Testing Library + jsdom, coverage con provider V8 [A] (PD-010).  
- Componentes, hooks y features con mocks del cliente API.

### 10.3 Base de datos de pruebas

- Base separada `gestion_tickets_test` o contenedor PostgreSQL.  
- Aplicar migraciones antes de integración.  
- Limpiar datos entre pruebas.

## 11. Git

### 11.1 Ramas

`main`, `feature/<desc>`, `fix/<desc>`, `docs/<desc>`.

### 11.2 Commits

Conventional Commits propuesto: `feat`, `fix`, `docs`, `refactor`, `test`, `chore`, `build`, `ci`. Mensajes consistentes (actualmente español).

### 11.3 Pull requests

Descripción, referencia al incremento, verificación de ausencia de secretos, coherencia con especificación.

### 11.4 Ignorados

`bin/`, `obj/`, `node_modules/`, archivos de entorno con secretos, User Secrets, logs, archivos de IDE locales, `.env*` con valores sensibles. `.gitignore` debe cubrirlos al crear proyectos.

## 12. Uso de OpenCode

Revisar código generado, no incluir secretos en prompts/archivos, validar propuestas técnicas, no modificar especificación ni eliminar `/legacy` sin verificación.

## 13. Trazabilidad

Stack, zona horaria D-14/D-37, sin secretos en repositorio, PostgreSQL, seguridad según `security-and-sessions.md`.
