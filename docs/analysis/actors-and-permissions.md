# Actores, roles y permisos

> Fase 0 — análisis del sistema legado. Evidencia verificada contra `vistaAgente/index.php` (enrutador), `vistaAgente/headerNav.php` (navegación), las vistas y los controladores.

## 1. Actores identificados

### 1.1 Administrador — `tipousuario.idrol = 3`
Usuario con el conjunto completo de funcionalidades. En la interfaz se muestra como **"Administrador"** (`headerNav.php`, líneas 237–239) y accede además a los contadores de usuarios del panel (`inicio.php`, líneas 124–154).

### 1.2 Administrativo — `tipousuario.idrol = 4`
Usuario operativo. La interfaz lo etiqueta **"Administrativo"** (`headerNav.php`, líneas 241–245). Puede operar tareas, áreas, direcciones (sólo consulta) y estadísticas, pero **no** ve el menú de usuarios ni el botón de alta/edición de direcciones.

### 1.3 Usuario dado de baja
No es un rol del catálogo. Es un usuario cuya columna `usuario.idRol2` fue puesta en `NULL` y a quien se le guardó `motivoBaja` (`m_consultas.php::bajaUsuario`). Permanece en la tabla y puede ser reactivado.

> **Comportamiento observado y relevante:** el login (`autenticarUsuario`) **no** verifica `idRol2`; un usuario dado de baja que conozca su contraseña puede autenticarse y obtiene `$_SESSION['rol'] = NULL`. Ver BR-007 y Q-001.

### 1.4 Visitante no autenticado
No hay un actor "público" previsto. El único punto de entrada legítimo es `login.php`. Sin embargo, los controladores de escritura no validan sesión ni rol (ver §5 y Q-002).

### 1.5 Roles declarados en el esquema pero no utilizados
`mantenimiento.sql` sólo inserta los roles 3 y 4. El código no referencia ningún otro `idrol`. No hay evidencia de roles 1 o 2; se desconoce si existieron (ver Q-003).

## 2. Matriz de permisos observada

Leyenda: ✅ visible/ejecutable · ❌ no visible en la interfaz · ⚠️ restricción sólo de interfaz (el endpoint no valida rol).

| Funcionalidad | Administrador (3) | Administrativo (4) | Evidencia |
|---|---|---|---|
| Iniciar sesión | ✅ | ✅ | `c_login.php` |
| Ver panel de control y totales de tareas | ✅ | ✅ | `inicio.php` |
| Ver contadores de usuarios en el panel | ✅ | ❌ | `inicio.php:124` |
| Ver estadísticas (hoy / rango / año / total) | ✅ | ✅ | `headerNav.php:254-279` |
| Ver listado de tareas actuales (Pendiente + En Progreso) | ✅ | ✅ | `headerNav.php:294` |
| Ver tareas completadas (y completadas hoy) | ✅ | ✅ | `headerNav.php:297` |
| Ver tareas canceladas | ✅ | ✅ | `headerNav.php:300` |
| Ver tareas eliminadas | ✅ | ✅ | `headerNav.php:303` |
| Crear tarea | ✅ | ✅ | `listarTareasAdmin.php:232` |
| Ver detalle de tarea | ✅ | ✅ | `listarTareasAdmin.php:528` |
| Iniciar tarea (Pendiente → En Progreso) | ✅ | ✅ | `listarTareasAdmin.php:534` |
| Terminar tarea (En Progreso → Completo) | ✅ | ✅ | `listarTareasAdmin.php:543` |
| Cancelar tarea (Pendiente/En Progreso → Cancelado) | ✅ | ✅ | `listarTareasAdmin.php:553` |
| Editar tarea (sólo Pendiente/En Progreso) | ✅ | ✅ | `listarTareasAdmin.php:561` |
| Eliminar tarea (borrado lógico, cualquier estado listado) | ✅ | ✅ | `listarTareasAdmin.php:570`, `listarTareasAdmin.php:296` |
| Ver listado de áreas | ✅ | ✅ | `headerNav.php:307` |
| Crear área | ✅ | ✅ | `listaAreas.php:51` (sin control de rol) |
| Editar área | ✅ | ✅ | `listaAreas.php:68` (sin control de rol) |
| Ver listado de direcciones | ✅ | ✅ | `headerNav.php:311` |
| Crear dirección | ✅ | ❌ | `listaDirecciones.php:68` |
| Editar dirección | ✅ | ❌ | `listaDirecciones.php:124` |
| Eliminar dirección | ⚠️ endpoint existe, sin enlace en la UI | ⚠️ ídem | `c_eliminarDireccion.php` (no referenciado) |
| Agregar usuario | ✅ | ❌ | `headerNav.php:328` + `modalNuevoUsuario` |
| Ver listado de usuarios | ✅ | ❌ | `headerNav.php:331` |
| Ver listado de bajas | ✅ | ❌ | `headerNav.php:334` |
| Dar de baja usuario | ✅ | ❌ | `usuariosCargados.php:133` |
| Dar de alta (reactivar) usuario | ✅ | ❌ | `listarUsuariosBaja.php:103` |
| Blanquear contraseña de otro usuario | ✅ | ❌ | `headerNav.php:338` |
| Gestionar el propio perfil (datos + contraseña) | ✅ | ✅ | `headerNav.php:112`, `c_editarUsuario.php` |
| Cerrar sesión | ✅ | ✅ | `headerNav.php:113` |

## 3. Cómo se implementan los permisos

### 3.1 Rol en sesión
Al autenticarse, `c_login.php` guarda:

```php
$_SESSION['username']       = $user;                       // texto ingresado (usuario o correo)
$_SESSION['rol']            = $co->verificarTipoUsuario($user); // 3 o 4
$_SESSION['legajo']         = $co->listarlegajoUserActual($user);
$_SESSION['nombreApellido'] = $co->mostrarDatosUsuario($user);
$_SESSION['time']           = time();                      // control de inactividad
```

`verificarTipoUsuario` (`m_consultas.php:21-35`) resuelve el rol por `usuario` **o** `correo` y devuelve el `idrol`.

### 3.2 Restricciones en la interfaz
- `headerNav.php:313` envuelve todo el bloque "Usuarios" en `if ($_SESSION['rol'] == 3)`.
- `listaDirecciones.php:68` y `:124` ocultan alta/edición de direcciones al rol distinto de 3.
- `listarUsuariosBaja.php:5`, `usuariosCargados.php:5`, `blanqueoPass.php:5` y `listaAgentes.php:5` envuelven la página completa en `if ($_SESSION['rol'] == 3)`.
- `listarTareasAdmin.php`, `listaAreas.php`, `estadisticas.php` e `inicio.php` **no** restringen por rol (el panel sólo oculta los contadores de usuarios).

### 3.3 Ausencia de autorización en el servidor
Ningún controlador (`controlador/*.php`) hace `session_start()` + validación de `$_SESSION['rol']` antes de ejecutar la operación. Tampoco verifican que la petición provenga de la aplicación.

Consecuencia: las restricciones de rol son **cosméticas**. Un usuario autenticado con rol 4 que invoque directamente `controlador/c_agregarUsuario.php`, `c_bajaUsuario.php`, `c_altaAgente.php` o `c_cambiarPass.php` ejecuta la operación. Un visitante sin sesión también puede invocarlas si conoce la URL. Ver Q-002.

### 3.4 Sesión y expiración
- Cada vista ejecuta `if (!(time() - $_SESSION['time'] >= 3600))` y renueva `$_SESSION['time']`; si la diferencia es ≥ 3600 s, incluye `destroySession.php`.
- Las páginas incluyen `<meta http-equiv="refresh" content="3600">`, que recarga a la hora.
- `index.php` renueva la marca de tiempo en cada acción enrutada.
- `logout.php` ejecuta `session_destroy()`.
- No hay `session_regenerate_id()` (riesgo de fijación de sesión).
- No hay CSRF token en ningún formulario; varias acciones de escritura usan `GET` (`c_iniciarTarea.php`, `c_eliminarDireccion.php`).

## 4. Permisos sobre datos (más allá del rol)

| Dimensión | Comportamiento observado |
|---|---|
| Tareas | **Sin aislamiento**: cualquier usuario autenticado ve y opera todas las tareas, sin importar quién las creó o qué área involucran. |
| Datos personales | Cada usuario edita sólo su propio registro desde el menú de usuario. No hay edición de datos de terceros (excepto blanqueo de contraseña y baja/alta). |
| Contraseñas | El administrador puede **blanquear** (fijar una nueva) la contraseña de cualquier usuario. No puede ver la existente (hash). |
| Catálogos | Áreas: ambos roles crean/editan. Direcciones: sólo el administrador crea/edita. |
| Archivos | Los comprobantes PDF quedan accesibles por URL directa (`../comprobantes/<archivo>`), sin control de sesión. |

## 5. Observaciones que condicionan la migración

1. **No existe el concepto de "agente asignado"**. Aunque la interfaz habla de "Agentes" (`listaAgentes.php`, título de `listarAreasContr`, mensajes de sesión), el modelo de datos no tiene `usuario_asignado_id`; `iniciarTarea` sólo cambia el estado. Cualquier funcionalidad de asignación sería **nueva**, no una migración.
2. El rol se guarda en sesión al iniciar sesión y **no se revalida**; si un administrador cambia el rol de un usuario, el cambio impacta recién en el próximo login.
3. La baja de usuario no invalida sesiones activas ni credenciales.
4. `c_editarUsuario.php` recibe `rol` pero **no lo usa**; la autogestión nunca cambia el rol (correcto desde el negocio, pero conviene hacerlo explícito en la nueva implementación).

## 6. Preguntas abiertas relacionadas

- ¿La baja de usuario debe impedir el login? (Q-001)
- ¿Debe existir autorización real por rol en el backend? (Q-002)
- ¿Los roles 3 y 4 son los únicos del negocio? (Q-003)
- ¿Debe un administrativo poder crear/editar áreas? (Q-004)
- ¿Las tareas deben ser visibles para todos o sólo para su creador/área? (Q-005)
