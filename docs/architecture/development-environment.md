# Entorno de desarrollo — Sistema de Gestión de Tickets

> **Incremento F2-I6 — Entorno de desarrollo, pruebas y documentación técnica.**
> Entorno previsto, sin credenciales reales; su documentación no supone el inicio del incremento.
> **Ajustes de F2-I2:** el backend y el esqueleto del frontend ya existen; los comandos de backend y frontend (restore/install, build, test y ejecución HTTPS) quedaron validados. PostgreSQL, EF Core y `dotnet-ef` continúan pendientes de F2-I3.

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

- `global.json` en `backend/` fijando el SDK: `{ "sdk": { "version": "10.0.403", "rollForward": "latestPatch", "allowPrerelease": false } }`.  
- `latestPatch` permite aplicar parches del SDK sin cambiar la versión aprobada; PD-021 considera los parches mantenimiento de rutina. Si el equipo tiene instalado, por ejemplo, `10.0.404`, ese parche se usa automáticamente.  
- Manifiesto local de herramientas: `backend/.config/dotnet-tools.json` con `dotnet-ef` en versión fijada. Se crea en F2-I3 junto con la persistencia; no forma parte de F2-I2.  
- Instalación de herramientas: `dotnet tool restore` (después de F2-I3).

## 3. .NET SDK y herramientas

Desde `backend/`:

```bash
dotnet --version
dotnet --list-sdks
dotnet tool restore       # disponible a partir de F2-I3
dotnet ef --version       # disponible a partir de F2-I3
```

Extensiones VS Code: C# Dev Kit, .NET, ESLint, Prettier, GitLens (opcional), REST Client (opcional).

Comandos:

```bash
dotnet restore GestionTickets.sln
dotnet build GestionTickets.sln
dotnet test GestionTickets.sln
dotnet run --project src/GestionTickets.Api --launch-profile https
```

Los comandos de migración se incorporan con F2-I3:

```bash
dotnet ef migrations add <Nombre> --project src/GestionTickets.Infrastructure --startup-project src/GestionTickets.Api
dotnet ef database update --project src/GestionTickets.Infrastructure --startup-project src/GestionTickets.Api
```

## 4. PostgreSQL

- Instalar PostgreSQL 18 (versión aprobada, PD-011).  
- Crear usuario y base `gestion_tickets_dev`.  
- No usar `postgres` con contraseña vacía en desarrollo compartido.  
- Conexión: `Host=localhost;Port=5432;Database=gestion_tickets_dev;Username=<usuario_dev>;Password=<secret>` (sin credenciales reales).  
- Herramientas: `psql`, pgAdmin/DBeaver (opcional).

## 5. EF Core y migraciones

- Paquetes NuGet en `Infrastructure` y `Api` (F2-I3).  
- Migraciones versionadas en `Infrastructure/Persistence/Migrations/`.  
- Desarrollo: `dotnet ef database update`.  
- Producción: pipeline controlado (decisión operativa).  
- No editar migraciones aplicadas.

## 6. Node.js y gestor de paquetes

- Instalar Node.js 24 LTS o superior (PD-008).
- El frontend declara la versión en `frontend/.nvmrc` (`24`) y en `engines` de `frontend/package.json` (`>=24.15.0`).
- Verificar: `node --version`, `pnpm --version`.  
- Usar pnpm con `pnpm-lock.yaml`; se activa vía `corepack enable` (PD-009); instalaciones reproducibles con `pnpm install --frozen-lockfile`. `package.json` declara `packageManager: pnpm@10.33.2`.

Comandos:

```bash
pnpm install --frozen-lockfile
pnpm dev
pnpm build
pnpm test
pnpm test:coverage
```

## 7. VS Code

Extensiones: C# Dev Kit, .NET, ESLint, Prettier, GitLens (opcional), REST Client (opcional).  
Configuración de workspace versionada sin secretos. Depuración backend mediante launch config.

## 8. Configuración local

- Backend: `appsettings.json` (no sensibles) + `appsettings.Development.json` + User Secrets.  
- Frontend: `.env.development` y `.env.example` (sin secretos, URL base de API) se incorporan con el cliente HTTP; en F2-I2 todavía no existen.
- Variables de entorno en producción.  
- No se versionan secretos.

CORS es obligatorio en **todos** los entornos (F2-I2):

```json
{
  "Cors": {
    "AllowedOrigins": ["https://localhost:5173"]
  }
}
```

- `Cors:AllowedOrigins` no tiene valor por defecto. La API valida la allowlist al arrancar y **no inicia** si falta, está vacía o contiene un origen inválido.
- Solo se aceptan orígenes absolutos HTTPS, sin comodines, ruta, query, fragmento ni barra final. Un comodín tampoco es compatible con `AllowCredentials`.
- `appsettings.Development.json` declara el origen local `https://localhost:5173`; `appsettings.json` queda vacío para no habilitar orígenes por defecto en otros entornos.
- En despliegues, la allowlist se configura por variables de entorno. Las colecciones usan claves indexadas:

```bash
Cors__AllowedOrigins__0=https://app.example.com
Cors__AllowedOrigins__1=https://admin.example.com
```

- La ausencia de estas variables en un entorno distinto de Development impide el arranque; es una falla deliberada y segura, no un fallback silencioso.

Frontend (F2-I2): el cliente HTTP y los archivos `.env.development`/`.env.example` quedan pendientes de un incremento posterior, todavía sin asignación en el plan actual. La URL base prevista es `https://localhost:5001` con prefijo `/api/v1`.

User Secrets (a partir de F2-I3, cuando exista la cadena de conexión):

```bash
dotnet user-secrets init --project src/GestionTickets.Api
dotnet user-secrets set "ConnectionStrings:Default" "Host=localhost;Port=5432;Database=gestion_tickets_dev;Username=<usuario>;Password=<secret>" --project src/GestionTickets.Api
```

## 9. Ejecución local y topología segura

- PostgreSQL primero.  
- HTTPS ya está configurado en backend (`https://localhost:5001`) y frontend (`https://localhost:5173` con Vite y `@vitejs/plugin-basic-ssl`; certificado autofirmado de desarrollo).
- CORS permite origen exacto `https://localhost:5173` con credenciales.  
- React usa `credentials: 'include'`.  
- La confianza formal de los certificados de desarrollo no está resuelta. El backend requiere `dotnet dev-certs https --trust` y el frontend requiere aceptar/confiar una vez el certificado autofirmado de `@vitejs/plugin-basic-ssl`. La resolución reproducible queda pendiente de F2-I6.
- Este estado no modifica PD-005 ni los requisitos de `docs/architecture/security-design.md` §11.1. El requisito de certificados confiados no debe declararse cumplido hasta que se valide.
- Cookie `Secure=true`, `SameSite=Lax`, host-only.  
- Esta topología evita debilitar seguridad de producción.

## 10. Pruebas

### 10.1 Backend

- Framework: xUnit.  
- Proyecto materializado en F2-I2: `backend/tests/GestionTickets.Api.Tests/`.  
- Pruebas de arranque con `WebApplicationFactory<Program>`: host levantado y ruta inexistente `404`.  
- Pruebas de configuración: validación de orígenes CORS y preflight permitido/rechazado.  
- Unitarias de dominio (invariantes, máquina de estados, elegibilidad) se agregan con el dominio.  
- Aplicación con repositorios en memoria/mocks.  
- Integración con base de datos de prueba y autenticación (F2-I3/F2-I4).

### 10.2 Frontend

- Framework: Vitest + React Testing Library + jsdom, coverage con provider V8 [A] (PD-010; materializado en F2-I2).
- Comandos desde `frontend/`: `pnpm test` y `pnpm test:coverage`.
- Prueba de humo del shell en `src/app/__tests__/App.test.tsx`; no hay pruebas de funcionalidad de negocio todavía.
- Componentes, hooks y features con mocks del cliente API se agregan con cada funcionalidad.

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
