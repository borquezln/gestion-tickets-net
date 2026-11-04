# Inventario de funcionalidades existentes

> Fase 0 — inventario verificado contra el enrutador (`vistaAgente/index.php`), la navegación (`headerNav.php`) y el esquema SQL.
> Estados: **Activa** (accesible y funcional desde la UI), **Parcial** (existe pero con defectos verificables), **Sólo endpoint** (existe en el backend pero no se ofrece en la UI), **Huérfana** (código no referenciado o incompatible con el esquema).

## 1. Mapa de entrada de la aplicación

### 1.1 Punto de entrada público
| Recurso | Descripción | Estado |
|---|---|---|
| `vistaAgente/login.php` | Formulario de acceso (usuario o correo + contraseña) | Activa |
| `controlador/c_login.php` | Procesa el login, crea la sesión, registra último acceso | Activa (sin rate limiting) |
| `vistaAgente/logout.php` | Destruye la sesión | Activa |
| `vistaAgente/destroySession.php` | Pantalla de sesión caducada | Activa |

### 1.2 Enrutador interno
`vistaAgente/index.php` expone un `switch ($_GET['accion'])` con **8 acciones**:

| Acción | Controlador | Vista | Estado |
|---|---|---|---|
| `verEstadisticas` | `c_admin.php::verEstadisticasContr` | `estadisticas.php` | Parcial (ver §6) |
| `listarTareasAdmin` | `c_admin.php::listarTareasAdminContr` | `listarTareasAdmin.php` | Activa |
| `listarTareasEliminadas` | `c_admin.php::listarTareasEliminadasContr` | `listaTareasEliminadas.php` | Activa |
| `listarAreas` | `c_admin.php::listarAreasContr` | `listaAreas.php` | Activa |
| `listarDirecciones` | `c_admin.php::listarDireccionesContr` | `listaDirecciones.php` | Activa |
| `listarBajas` | `c_admin.php::listarUsuariosBajaContr` | `listarUsuariosBaja.php` | Activa (sólo rol 3) |
| `listarUsuarios` | `c_admin.php::listarUsuariosCargadosContr` | `usuariosCargados.php` | Activa (sólo rol 3) |
| `blanqueoPass` | `c_admin.php::pageBlanquearPassContr` | `blanqueoPass.php` | Parcial (ver §5) |
| `default` | — | `history.go(-1)` | — |

> Cualquier otra acción cae en `default` y no hace nada útil. En particular, **no existe** `accion=editarTarea`, aunque `filtroRadios.php` enlaza a ella.

---

## 2. Autenticación y sesión

| ID | Funcionalidad | Descripción | Evidencia | Estado |
|---|---|---|---|---|
| CF-01 | Login con usuario o correo | Autenticación con `password_verify` | `c_login.php`, `m_consultas.php::autenticarUsuario` | Activa |
| CF-02 | Registro de último acceso | Actualiza `ultimoAcceso` en cada login | `m_consultas.php::verificarUltimoAcceso` | Parcial (falla si el login es por correo) |
| CF-03 | Sesión con rol y datos del usuario | Guarda username, rol, legajo, nombre, timestamp | `c_login.php` | Activa |
| CF-04 | Expiración por inactividad (60 min) | Destruye la sesión y muestra aviso | vistas + `destroySession.php` | Activa |
| CF-05 | Logout | Destruye la sesión y vuelve al login | `logout.php` | Activa |
| CF-06 | Mensaje de credenciales erróneas | Alerta en el login | `login.php`, `errLogin` | Activa |
| CF-07 | Mensaje de sesión caducada | Alerta en el login | `login.php`, `session_caducada` | Activa |
| CF-08 | Autogestión del perfil | Modal para editar datos propios y contraseña | `headerNav.php`, `c_editarUsuario.php` | Parcial (falla si el login fue por correo) |

**No existe:** registro público, recuperación de contraseña por correo, bloqueo por intentos, MFA, "recordarme", regeneración de ID de sesión, CSRF.

---

## 3. Panel de control

| ID | Funcionalidad | Descripción | Evidencia | Estado |
|---|---|---|---|---|
| CF-09 | Total de tareas | Cuenta todas las filas de `tareas` (incluye eliminadas) | `inicio.php:41` | Activa |
| CF-10 | Contadores por estado | Tarjetas Pendiente, En Progreso, Completo, Cancelado, Eliminado | `inicio.php:42-46` | Activa |
| CF-11 | Acceso rápido a listados | Cada tarjeta enlaza al listado correspondiente | `inicio.php:68-120` | Activa |
| CF-12 | Contadores de usuarios (sólo admin) | Total, administradores, administrativos | `inicio.php:124-154` | Activa |
| CF-13 | Total de direcciones/entidades | Tarjeta con `COUNT(*)` de `direcciones` | `inicio.php:156-168` | Activa |
| CF-14 | Saludo personalizado | "Bienvenido <Nombre Apellido>" | `inicio.php:56` | Activa |
| CF-15 | Fecha actual en la barra lateral | Fecha formateada `d/m/Y` | `headerNav.php:346-354` | Parcial (variable no inicializada; depende del valor por defecto de `date_create(null)`) |

**No existe:** gráficos (Chart.js se carga pero no se usa), filtros del panel, tareas recientes, avisos.

---

## 4. Tareas / requerimientos

| ID | Funcionalidad | Descripción | Evidencia | Estado |
|---|---|---|---|---|
| CF-16 | Crear tarea | Alta con área, descripción, dirección y datos opcionales | `c_agregarTarea.php`, `m_consultas.php::agregarTarea` | Parcial (nota electrónica nunca se guarda) |
| CF-17 | Listar tareas actuales | Pendiente + En Progreso | `m_consultas.php::listarTareasAdmin('actual')` | Activa |
| CF-18 | Listar tareas completadas | Estado 3, con sublistado de hoy | `listarTareasAdmin('completas')`, `listarTareasCompletasActual` | Activa |
| CF-19 | Listar tareas canceladas | Estado 4, sólo lectura | `listarTareasAdmin('canceladas')` | Activa |
| CF-20 | Listar tareas eliminadas | Estado 5, con motivo y fecha | `listarTareasEliminadas` | Activa |
| CF-21 | Ver detalle de tarea | Modal de sólo lectura | `listarTareasAdmin.php`, `listaTareasEliminadas.php` | Activa |
| CF-22 | Iniciar tarea | Pendiente → En Progreso | `c_iniciarTarea.php` | Activa (sin registrar responsable/fecha) |
| CF-23 | Terminar tarea | En Progreso → Completo + solución + PDF + fecha | `c_terminarTarea.php` | Parcial (sin validación server-side del archivo/estado) |
| CF-24 | Cancelar tarea | → Cancelado con motivo | `c_cancelarTarea.php` | Activa |
| CF-25 | Editar tarea | Modifica datos de tareas Pendiente/En Progreso | `c_editarTarea.php` | Parcial (borra la nota electrónica) |
| CF-26 | Eliminar tarea (lógica) | → Eliminado con motivo y fecha | `c_eliminarTarea.php` | Activa |
| CF-27 | Descargar comprobante | Enlace de descarga del PDF en el detalle | `listarTareasAdmin.php:370-372` | Activa (sin control de acceso al archivo) |
| CF-28 | Exportar a Excel | Exportación cliente con DataTables | `listarTareasAdmin.php:41-48` | Activa |
| CF-29 | Exportar a PDF | Exportación cliente, A4 horizontal | `listarTareasAdmin.php:49-59` | Activa |
| CF-30 | Orden y paginación de tablas | DataTables en todas las tablas | `libreriaEstilos.php`, vistas | Activa |
| CF-31 | Validación de comillas simples en descripciones | Reemplaza `'` por `"` para evitar romper el SQL | `listarTareasAdmin.php:95-104` | Parcial (mitigación de cliente, no de servidor) |
| CF-32 | Validación de celular numérico | Sólo permite dígitos y puntos | `listarTareasAdmin.php:88-93` | Activa (cliente) |
| CF-33 | Nota electrónica condicional | Checkbox que muestra/oculta y hace obligatorio el campo | `listarTareasAdmin.php:77-86` | Activa en UI; **no persiste** |

**No existe:** asignación a un usuario, prioridad, categoría, SLA, comentarios, adjuntos múltiples, búsqueda, filtros, edición después de completar/cancelar, reapertura, restauración, historial.

---

## 5. Usuarios y roles

| ID | Funcionalidad | Descripción | Evidencia | Estado |
|---|---|---|---|---|
| CF-34 | Crear usuario | Alta con rol, legajo, nombre, apellido, correo opcional, usuario y contraseña | `c_agregarUsuario.php` | Activa |
| CF-35 | Validar usuario duplicado (AJAX) | Aviso en vivo y bloqueo del botón | `validarUsernameExistente.php` | Parcial (no autoritativo) |
| CF-36 | Listar usuarios | Inner join con rol; excluye bajas; muestra último acceso | `usuariosCargados.php` | Activa |
| CF-37 | Dar de baja usuario | Baja lógica con motivo | `c_bajaUsuario.php` | Activa |
| CF-38 | Listar usuarios de baja | Filtra `idRol2 IS NULL` | `listarUsuariosBaja.php` | Activa |
| CF-39 | Dar de alta (reactivar) usuario | Asigna rol y limpia motivo | `c_altaAgente.php` | Activa |
| CF-40 | Blanquear contraseña | Admin fija nueva contraseña a un usuario | `c_cambiarPass.php` | Parcial (lee el campo equivocado) |
| CF-41 | Gestionar usuario actual | Edición del propio perfil | `c_editarUsuario.php` | Parcial |
| CF-42 | Listar roles | Catálogo `tipousuario` | `m_consultas.php::listarRoles` | Activa |
| CF-43 | Mostrar rol en el menú | "Administrador" / "Administrativo" | `headerNav.php:235-247` | Activa |

**No existe:** edición de usuarios de terceros, cambio directo de rol, historial de accesos (sólo el último), invitaciones, política de contraseñas, bloqueo de cuentas.

---

## 6. Estadísticas

| ID | Funcionalidad | Descripción | Evidencia | Estado |
|---|---|---|---|---|
| CF-44 | Estadísticas de hoy | Totales por estado y por área con `fechaCreada`/`fechaProblema` | `verEstadisticasContr('now')` | Parcial (conteo por estado roto) |
| CF-45 | Estadísticas por rango de fechas | Modal con fecha inicio/fin | `verEstadisticasContr('month')` | Parcial (render roto, ver CF-48) |
| CF-46 | Estadísticas del año actual | Filtro por `YEAR(fechaCreada)` | `verEstadisticasContr('year')` | Parcial |
| CF-47 | Estadísticas históricas | Totales sin filtro | `verEstadisticasContr('all')` | Parcial |
| CF-48 | Tabla por estado y por área | Render de resultados | `estadisticas.php` | Parcial (accede a `[0][0]` sobre escalares) |

**No existe:** gráficos, exportación de estadísticas, comparativas, filtros por dirección o por usuario.

---

## 7. Áreas

| ID | Funcionalidad | Descripción | Evidencia | Estado |
|---|---|---|---|---|
| CF-49 | Listar áreas | `SELECT * FROM areas` | `listaAreas.php` | Activa |
| CF-50 | Crear área | Nombre y descripción obligatorios | `c_agregarArea.php` | Activa |
| CF-51 | Editar área | Nombre y descripción | `c_editarArea.php` | Activa |
| CF-52 | Validar área duplicada | **No implementado** para áreas (sólo para direcciones) | — | Inexistente |

**No existe:** eliminar área, deshabilitar área, jerarquía de áreas.

---

## 8. Direcciones / Entidades

| ID | Funcionalidad | Descripción | Evidencia | Estado |
|---|---|---|---|---|
| CF-53 | Listar direcciones | Ordenadas por nombre | `listaDirecciones.php` | Activa |
| CF-54 | Crear dirección (admin) | Código manual + nombre + descripción | `c_agregarDireccion.php` | Activa |
| CF-55 | Validar código duplicado (AJAX) | Aviso y bloqueo del botón | `validarAreaExistente.php` | Parcial (bloquea también la edición del propio registro) |
| CF-56 | Editar dirección (admin) | Permite cambiar el código | `c_editarDireccion.php` | Activa con riesgo de FK |
| CF-57 | Eliminar dirección | `DELETE` físico | `c_eliminarDireccion.php` | **Sólo endpoint** (sin enlace en la UI) |

**No existe:** baja lógica, deshabilitar dirección, relación con áreas.

---

## 9. Funcionalidades huérfanas o incompatibles

| ID | Archivo / función | Qué hace | Por qué no está vigente |
|---|---|---|---|
| CF-H01 | `vistaAgente/listaAgentes.php` | Listado de agentes con baja | Usa `$listEncAgentes` (no definido) y `c_bajaAgente.php` (no existe); no se incluye desde ningún controlador. |
| CF-H02 | `vistaAgente/ajax/filtroEstado.php` | Filtro de tareas por estado y agente | Consulta `t.usuario_legajo`, columna inexistente; sin referencias. |
| CF-H03 | `vistaAgente/ajax/filtroRadios.php` | Filtro por "mis pendientes / en progreso / completas" | Consulta `t.area_codigo` y `t.nombreAfectado`, inexistentes; enlaza a una acción no implementada. |
| CF-H04 | `vistaAgente/ajax/buscarPorFecha.php` | Búsqueda por fecha de problema/solución | Consulta `t.usuario_legajo`, inexistente; sin referencias. |
| CF-H05 | `m_consultas.php::listarDireccionesCodigo` | Obtiene una dirección por código | Sin llamadas. |
| CF-H06 | `m_consultas.php::listarUsuarios` | Lista usuarios con rol | Sólo usado por el blanqueo de contraseña. |
| CF-H07 | `scripts/alerta.php` | Helper de SweetAlert | Activo, usado por todas las vistas. |
| CF-H08 | `styles/headerNav.css` | Estilos del menú | Activo. |
| CF-H09 | Chart.js en `inicio.php` | Librería de gráficos | Cargada sin uso. |

---

## 10. Resumen cuantitativo

| Categoría | Cantidad |
|---|---|
| Funcionalidades activas o parciales identificadas | 57 (CF-01 a CF-57) |
| Funcionalidades sólo-endpoint | 1 (CF-57) |
| Componentes huérfanos/incompatibles | 9 (CF-H01 a CF-H09) |
| Acciones del enrutador | 8 |
| Controladores PHP | 18 |
| Vistas PHP | 16 |
| Tablas de base | 6 |
| Estados de tarea | 5 |
| Roles | 2 |
| Reglas de negocio documentadas | 54 |

## 11. Brechas funcionales relevantes para la migración

1. **No hay asignación de tareas**: si el negocio espera "agentes" (la UI lo insinúa), es una funcionalidad **faltante**, no existente.
2. **No hay historial**: la trazabilidad se limita a fechas sueltas y al nombre del creador.
3. **No hay búsqueda ni filtros**: con volumen alto, los listados se vuelven inmanejables (sólo orden/paginación de cliente).
4. **La nota electrónica no funciona**: campo visible que no se persiste.
5. **La baja de usuario rompe la visibilidad de sus tareas**.
6. **Las estadísticas por estado "de hoy" y el render de la tabla están defectuosos**.
7. **La autorización es sólo visual**: debe rediseñarse por completo en ASP.NET Core.
8. **No hay API ni contrato de datos**: el frontend React requerirá definir DTOs y endpoints desde cero, manteniendo las reglas de `business-rules.md`.
