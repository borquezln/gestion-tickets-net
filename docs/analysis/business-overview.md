# Descripción general del negocio

> Documento de Fase 0 (análisis funcional y técnico del sistema legado).
> Basado exclusivamente en el código PHP, el esquema SQL (`mantenimiento.sql`) y las vistas del repositorio.
> No se modificó ningún archivo del sistema original.

## 1. Qué es el sistema

`gestionDOA` (según `README.md`) es un **sistema de gestión de tareas / requerimientos de mantenimiento**, implementado en PHP puro sin framework y con MySQL/MariaDB como persistencia (base de datos `mantenimiento`).

La aplicación permite que personal administrativo de una organización:

1. Registre **requerimientos de mantenimiento** (llamados indistintamente *tareas*, *arreglos* o *requerimientos* en el código y la interfaz).
2. Los asocie a un **área** responsable y a una **dirección/entidad** donde se desempeña la persona afectada.
3. Siga su avance mediante un **ciclo de vida de cinco estados** (Pendiente → En Progreso → Completo, con ramas Cancelado y Eliminado).
4. Registre la solución aplicada ("Materiales utilizados"), la fecha de solución y un **comprobante PDF**.
5. Consulte **estadísticas** por período y por área.
6. Administre **usuarios**, **roles**, **áreas** y **direcciones/entidades**.

El nombre de la base (`mantenimiento`), los textos de la interfaz ("Gestión de tareas", "Gestión Requerimientos", "Requerimientos en total") y los campos de la entidad `tareas` confirman que el dominio es la **gestión de requerimientos de mantenimiento**, no un help desk genérico.

## 2. Contexto de uso observado en el código

- Aplicación **web de intranet**, monolítica, sin API. Todas las pantallas se renderizan en el servidor y se devuelven como HTML.
- El **frontend** es HTML + Bootstrap 5 + jQuery + DataTables + SweetAlert2, cargados por CDN.
- No hay separación real entre capas: los "controladores" reciben `$_POST`/`$_GET`, instancian el modelo y terminan con `header('Location: ...')`; los modelos ejecutan SQL concatenado.
- No existen pruebas automatizadas, migraciones, ni entorno de configuración por variables. La conexión está hardcodeada (`localhost`, `root`, sin contraseña, base `mantenimiento`).
- El sistema **no tiene API**: la nueva implementación ASP.NET Core + React implicará rediseñar por completo la capa de comunicación.

## 3. Módulos funcionales identificados

| Módulo | Propósito | Pantallas / endpoints |
|---|---|---|
| Autenticación y sesión | Login por usuario o correo, sesión PHP, expiración por inactividad, logout | `vistaAgente/login.php`, `controlador/c_login.php`, `vistaAgente/logout.php`, `destroySession.php` |
| Panel de control (dashboard) | Totales de tareas por estado, total de usuarios por rol, total de direcciones | `vistaAgente/inicio.php` |
| Tareas / requerimientos | Alta, listado por estado, detalle, edición, iniciar, terminar, cancelar, eliminar lógicamente | `vistaAgente/listarTareasAdmin.php`, `listaTareasEliminadas.php`, `controlador/c_*Tarea.php` |
| Áreas | Catálogo de áreas involucradas; alta y edición | `vistaAgente/listaAreas.php`, `controlador/c_agregarArea.php`, `c_editarArea.php` |
| Direcciones/Entidades | Catálogo de direcciones o entidades; alta, edición y (endpoint) eliminación | `vistaAgente/listaDirecciones.php`, `controlador/c_agregarDireccion.php`, `c_editarDireccion.php`, `c_eliminarDireccion.php` |
| Usuarios | Alta, listado, baja lógica, alta (reactivación), blanqueo de contraseña, autogestión del propio perfil | `vistaAgente/usuariosCargados.php`, `listarUsuariosBaja.php`, `blanqueoPass.php`, `controlador/c_*Usuario.php`, `c_altaAgente.php`, `c_cambiarPass.php` |
| Estadísticas | Conteos por estado y por área, para hoy / rango de fechas / año / histórico | `vistaAgente/estadisticas.php`, `controlador/c_admin.php::verEstadisticasContr`, `modelo/m_estadisticas.php` |
| Exportación | Exportar listados de tareas a Excel y PDF desde el cliente (DataTables Buttons) | `vistaAgente/libreriaEstilos.php`, `listarTareasAdmin.php` |

## 4. Entidades de negocio

| Entidad | Rol en el negocio |
|---|---|
| **Tarea / Requerimiento** (`tareas`) | Núcleo del sistema. Representa un problema de mantenimiento reportado por o para una persona afectada, con área y dirección involucradas. |
| **Estado de tarea** (`estadotarea`) | Catálogo cerrado de 5 estados: Pendiente, En Progreso, Completo, Cancelado, Eliminado. |
| **Área** (`areas`) | Área organizacional que lleva a cabo la tarea. |
| **Dirección / Entidad** (`direcciones`) | Dirección o entidad donde se desempeña la persona afectada. Código manual, no autoincremental. |
| **Usuario** (`usuario`) | Persona que opera el sistema. El legajo es la clave primaria. |
| **Tipo de usuario / Rol** (`tipousuario`) | Catálogo de roles: 3 = Administrador, 4 = Administrativo. |
| **Comprobante** | No es una tabla: es un archivo PDF en disco (`/comprobantes/`) cuya ruta se guarda en `tareas.comprobante`. |
| **Sesión** | Estado transitorio del servidor (`$_SESSION`), no persistido en base. |

Detalle completo en [`domain-model.md`](./domain-model.md).

## 5. Ciclo de vida de una tarea (resumen)

```
                    iniciar                    terminar
   [Pendiente] ───────────────► [En Progreso] ───────────────► [Completo]
        │                             │                            │
        │ cancelar                    │ cancelar                   │ eliminar
        ▼                             ▼                            ▼
   [Cancelado] ◄──────────────────────┘                      [Eliminado]
        │                                                          ▲
        └────────────────────── eliminar ──────────────────────────┘
```

- Toda tarea nace en **Pendiente** (estado 1).
- **Eliminado** es un borrado lógico: la fila permanece y sólo cambia de estado, guardando motivo y fecha.
- No existe reapertura ni restauración de tareas canceladas, completadas o eliminadas.
- El estado se cambia con `UPDATE` directo, sin historial de transiciones.
- La edición de datos está limitada en la interfaz a tareas **Pendiente** o **En Progreso**.

Detalle en [`workflows.md`](./workflows.md) y reglas en [`business-rules.md`](./business-rules.md).

## 6. Actores

| Actor | Descripción | Alcance |
|---|---|---|
| **Administrador** (rol 3) | Usuario con acceso a todos los módulos, incluida la gestión de usuarios. | Todo el sistema |
| **Administrativo** (rol 4) | Usuario operativo: tareas, áreas, direcciones (sólo consulta), estadísticas y su propio perfil. | Todo excepto gestión de usuarios y alta/edición de direcciones |
| **Usuario dado de baja** | Registro con `idRol2 = NULL` y `motivoBaja`. No aparece en listados operativos, pero **sus credenciales siguen siendo válidas** (comportamiento actual). | Sin rol asignado en sesión |
| **Visitante no autenticado** | Acceso directo a URLs. Las vistas redirigen/expiran, pero los controladores no validan sesión. | Potencialmente endpoints de escritura |

Detalle y matriz de permisos en [`actors-and-permissions.md`](./actors-and-permissions.md).

## 7. Reglas de negocio centrales (vista rápida)

1. Toda tarea requiere **área**, **descripción** y **dirección/entidad**; nota electrónica, afectado y celular son opcionales.
2. Toda tarea nace **Pendiente**, con `fechaProblema = NOW()` y `fechaCreada = CURDATE()`.
3. Completar una tarea exige **materiales utilizados** y un **comprobante PDF**; registra `fechaSolucion`.
4. Cancelar y eliminar exigen **motivo**; eliminar registra además `fechaEliminado` y es **lógico** (no borra filas).
5. Los estados son un catálogo cerrado de 5 valores; no hay estados configurables por el usuario.
6. **No existe asignación de tarea a un agente**: "iniciar" sólo cambia el estado. El único vínculo con personas es `usuarioCreado` (texto "Nombre Apellido").
7. Ambos roles ven **todas** las tareas del sistema; no hay aislamiento por creador, área o responsable.
8. Sólo el **Administrador** gestiona usuarios y da de alta/edita direcciones.
9. La baja de usuario es **lógica** (`idRol2 = NULL` + motivo) y es reversible mediante "Dar de alta".
10. No hay historial de cambios: se conserva únicamente el último valor de cada campo más algunas fechas.

## 8. Funcionalidades **no** evidenciadas en el código

Para evitar inventar alcance, se deja constancia de que el sistema **no** implementa (no hay código que lo respalde):

- Prioridad, severidad, categoría o SLA de tareas.
- Asignación de una tarea a un usuario/agente específico.
- Comentarios, adjuntos múltiples o conversación sobre la tarea.
- Notificaciones por correo o en aplicación.
- Historial/auditoría de estados o de ediciones.
- Búsqueda de texto, filtros por área/estado en la UI principal ni paginación server-side.
- API REST/JSON, tokens, ni integración con otros sistemas.
- Multi-tenant, multi-idioma ni configuración por entorno.
- Reportes programados; la exportación es manual y del lado del cliente.

## 9. Métricas del repositorio analizado

- 48 archivos totales; 18 controladores (`controlador/c_*.php`), 3 archivos de modelo, 16 vistas PHP y 2 scripts/utilidades.
- 6 tablas en base (`areas`, `direcciones`, `estadotarea`, `tareas`, `tipousuario`, `usuario`).
- 5 estados de tarea, 2 roles, 2 niveles de permiso efectivos.
- 54 reglas de negocio documentadas en [`business-rules.md`](./business-rules.md).

## 10. Advertencia de interpretación

Varios archivos y pantallas **no están conectados** al flujo actual (`listaAgentes.php`, `vistaAgente/ajax/*`, `c_eliminarDireccion.php`, `c_eliminarTarea` desde algunos listados). Sus nombres no deben tomarse como funcionalidad vigente: fueron verificados contra el enrutador real (`vistaAgente/index.php`) y el esquema SQL. Ver [`current-functionalities.md`](./current-functionalities.md) y [`open-questions.md`](./open-questions.md).
