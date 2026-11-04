# Catálogo de reglas de negocio

> Fase 0 — reglas reconstruidas desde el código PHP y el esquema SQL.
> **No se introducen reglas nuevas.** Toda regla indica su evidencia y su nivel de certeza.

## Leyenda de certeza

| Nivel | Significado |
|---|---|
| **Explícita** | El código implementa la regla de forma directa y verificable (consulta, condición, validación o flujo). |
| **Inferida** | La regla se deduce del comportamiento observado o de una consecuencia del diseño, pero no está escrita ni validada como intención de negocio. Requiere confirmación. |
| **Pendiente de confirmar** | Hay evidencia contradictoria, incompleta o un comportamiento claramente defectuoso cuya intención no puede determinarse sin intervención humana. |

## Resumen

| Grupo | Rango | Cantidad |
|---|---|---|
| Autenticación y sesión | BR-001 – BR-007 | 7 |
| Roles y permisos | BR-008 – BR-013 | 6 |
| Usuarios | BR-014 – BR-023 | 10 |
| Tareas / requerimientos | BR-024 – BR-042 | 19 |
| Catálogos (áreas y direcciones) | BR-043 – BR-047 | 5 |
| Estadísticas y reportes | BR-048 – BR-052 | 5 |
| Trazabilidad y otras | BR-053 – BR-054 | 2 |
| **Total** | | **54** |

---

# A. Autenticación y sesión

## BR-001 — Identificación por usuario o correo
- **Descripción:** Un usuario puede autenticarse indistintamente con su nombre de usuario o con su correo electrónico. El sistema busca en ambas columnas con la misma cadena.
- **Condición:** Envío del formulario de login con `user` y `password`.
- **Comportamiento esperado:** Se busca una fila en `usuario` donde `usuario = <valor>` **o** `correo = <valor>`. Si hay coincidencia y la contraseña verifica, se concede el acceso.
- **Entidades:** Usuario.
- **Evidencia:** `modelo/m_consultas.php::autenticarUsuario` (línea 11).
- **Certeza:** Explícita.

## BR-002 — Contraseñas almacenadas con hash
- **Descripción:** Las contraseñas no se almacenan en texto plano; se usa el hash por defecto de PHP (`password_hash`, bcrypt) y se verifica con `password_verify`.
- **Condición:** Alta de usuario, blanqueo de contraseña, cambio de contraseña propia y login.
- **Comportamiento esperado:** Al crear o cambiar la contraseña se guarda `password_hash($pass, PASSWORD_DEFAULT)`. En el login se compara con `password_verify`.
- **Entidades:** Usuario.
- **Evidencia:** `m_consultas.php::agregarUsuario` (118), `::editarDatosUsuario` (214), `::blanquearPass` (241), `::autenticarUsuario` (14).
- **Certeza:** Explícita.

## BR-003 — La sesión almacena identidad, rol, legajo y nombre
- **Descripción:** Tras un login exitoso, la sesión conserva el identificador ingresado, el rol, el legajo y el nombre completo del usuario, y los usa para personalizar la interfaz y autorizar acciones.
- **Condición:** Login exitoso.
- **Comportamiento esperado:** Se escriben `$_SESSION['username']`, `rol`, `legajo`, `nombreApellido`, `time`. El rol se resuelve con `verificarTipoUsuario`; el legajo con `listarlegajoUserActual`; el nombre con `mostrarDatosUsuario`.
- **Entidades:** Usuario, Rol, Sesión.
- **Evidencia:** `controlador/c_login.php` (9-13).
- **Certeza:** Explícita.

## BR-004 — Registro del último acceso
- **Descripción:** Cada login exitoso actualiza la fecha y hora del último acceso del usuario.
- **Condición:** Login exitoso.
- **Comportamiento esperado:** `UPDATE usuario SET ultimoAcceso = now() WHERE usuario = '<identificador>'`.
- **Entidades:** Usuario.
- **Evidencia:** `m_consultas.php::verificarUltimoAcceso` (161-171); invocado en `c_login.php` (15).
- **Certeza:** Explícita.
- **Observación:** Si el usuario se autenticó con correo, el `WHERE usuario = '<correo>'` no actualiza ninguna fila (mismo defecto que BR-021).

## BR-005 — Expiración de sesión por inactividad de 60 minutos
- **Descripción:** La sesión caduca tras 60 minutos sin actividad. Cada vista evalúa la diferencia entre la hora actual y `$_SESSION['time']`; si es mayor o igual a 3600 segundos, destruye la sesión y muestra un mensaje de caducidad.
- **Condición:** Acceso a cualquier vista protegida.
- **Comportamiento esperado:** Si `time() - $_SESSION['time'] < 3600`, se renueva `$_SESSION['time']` y se renderiza; en caso contrario se ejecuta `destroySession.php`.
- **Entidades:** Sesión.
- **Evidencia:** `vistaAgente/index.php` (5-6, 67-69), `inicio.php` (4-5), `listarTareasAdmin.php` (4-5), `estadisticas.php` (3-4), etc.; `vistaAgente/destroySession.php`.
- **Certeza:** Explícita.
- **Observaciones:** Es un control por página, no por petición ni por middleware; las acciones de los controladores no lo verifican. `<meta refresh content="3600">` recarga las páginas cada hora. Ver Q-012.

## BR-006 — Cierre de sesión
- **Descripción:** El usuario puede cerrar sesión explícitamente; la acción destruye la sesión y lo devuelve al login.
- **Condición:** Clic en "Cerrar sesión".
- **Comportamiento esperado:** `session_start(); session_destroy(); header('Location: login.php')`.
- **Entidades:** Sesión.
- **Evidencia:** `vistaAgente/logout.php`; enlace en `headerNav.php` (113).
- **Certeza:** Explícita.

## BR-007 — La baja de usuario **no** invalida el acceso
- **Descripción:** Un usuario dado de baja (`idRol2 = NULL`) conserva su contraseña y su nombre de usuario; el login no comprueba el rol ni el estado de baja, por lo que puede seguir autenticándose. Su sesión queda con `rol = NULL`.
- **Condición:** Login de un usuario con `idRol2 IS NULL`.
- **Comportamiento esperado (actual):** El login es exitoso; `verificarTipoUsuario` no devuelve un rol válido; la navegación no muestra el menú (porque `isset($_SESSION['rol'])` es falso para `NULL`) pero `inicio.php` sigue accesible, y los controladores de escritura no bloquean la operación.
- **Entidades:** Usuario, Rol, Sesión.
- **Evidencia:** `m_consultas.php::autenticarUsuario` (no filtra por `idRol2`); `::verificarTipoUsuario` (21-35); `headerNav.php` (4).
- **Certeza:** **Pendiente de confirmar** (el comportamiento está verificado; se desconoce si es intencional). Ver Q-001.

---

# B. Roles y permisos

## BR-008 — Rol Administrador (id 3) con acceso completo
- **Descripción:** El rol 3 ("Administrador") accede a todas las funcionalidades: tareas, áreas, direcciones (alta/edición), estadísticas, usuarios (alta, listado, bajas, altas, blanqueo) y autogestión.
- **Condición:** `$_SESSION['rol'] == 3`.
- **Comportamiento esperado:** Se renderizan los menús y acciones administrativas; el panel muestra los contadores de usuarios.
- **Entidades:** Usuario, Rol.
- **Evidencia:** `headerNav.php` (237-239, 313-341); `inicio.php` (124-154); `listaDirecciones.php` (68, 124); `usuariosCargados.php` (5); `listarUsuariosBaja.php` (5); `blanqueoPass.php` (5).
- **Certeza:** Explícita (en la interfaz; ver BR-013).

## BR-009 — Rol Administrativo (id 4) sin gestión de usuarios
- **Descripción:** El rol 4 ("Administrativo") opera tareas, áreas, consulta direcciones y estadísticas, pero no accede a la gestión de usuarios, al blanqueo de contraseñas ni al alta/edición de direcciones.
- **Condición:** `$_SESSION['rol'] == 4` (o cualquier valor distinto de 3).
- **Comportamiento esperado:** No se muestran las opciones administrativas; la etiqueta del menú dice "Administrativo".
- **Entidades:** Usuario, Rol.
- **Evidencia:** `headerNav.php` (241-245, 313-341); `listaDirecciones.php` (68, 124).
- **Certeza:** Explícita (en la interfaz; ver BR-013).

## BR-010 — Gestión de usuarios exclusiva del Administrador
- **Descripción:** Sólo el Administrador puede agregar usuarios, ver el listado de usuarios, ver las bajas, dar de baja, dar de alta y blanquear contraseñas.
- **Condición:** Rol 3 y acceso a las acciones correspondientes.
- **Comportamiento esperado:** Las pantallas y formularios están disponibles; las acciones se ejecutan sobre `usuario`.
- **Entidades:** Usuario, Rol.
- **Evidencia:** `headerNav.php` (313-341); `usuariosCargados.php` (5, 133); `listarUsuariosBaja.php` (5, 103); `blanqueoPass.php` (5).
- **Certeza:** Explícita en la interfaz; **no aplicada en el servidor** (ver BR-013).

## BR-011 — Direcciones: consulta para ambos roles, alta/edición sólo para el Administrador
- **Descripción:** Cualquier usuario autenticado puede ver el catálogo de direcciones/entidades; sólo el Administrador puede agregar o editar.
- **Condición:** Acceso a `index.php?accion=listarDirecciones` / uso de los modales de alta y edición.
- **Comportamiento esperado:** La tabla se muestra a ambos; el botón "Agregar Dirección", la columna "Acción" y el modal de edición se renderizan sólo para rol 3.
- **Entidades:** Dirección, Usuario, Rol.
- **Evidencia:** `headerNav.php` (311); `listaDirecciones.php` (67-75, 123-130, 140-160).
- **Certeza:** Explícita en la interfaz; no aplicada en `c_agregarDireccion.php` / `c_editarDireccion.php`.

## BR-012 — Áreas: alta y edición disponibles para ambos roles
- **Descripción:** No hay control de rol en el módulo de áreas: tanto Administrador como Administrativo ven y usan los botones "Nueva área" y "Editar".
- **Condición:** Acceso a `index.php?accion=listarAreas`.
- **Comportamiento esperado:** Se muestran el listado y ambos formularios sin condicionar por `$_SESSION['rol']`.
- **Entidades:** Área, Usuario, Rol.
- **Evidencia:** `listaAreas.php` (50-52, 67-69) — no hay ninguna condición de rol en el archivo; `c_admin.php::listarAreasContr` (115-124).
- **Certeza:** **Inferida** (es una consecuencia de la ausencia de control; se desconoce si la intención era permitirlo). Ver Q-004.

## BR-013 — Las restricciones de rol son sólo de interfaz
- **Descripción:** Las limitaciones por rol se implementan ocultando opciones en las vistas. Ningún controlador valida sesión, rol ni origen de la petición antes de ejecutar una operación.
- **Condición:** Invocación directa de cualquier URL de `controlador/`.
- **Comportamiento esperado (actual):** La operación se ejecuta aunque el usuario no tenga el rol requerido o no haya iniciado sesión.
- **Entidades:** Usuario, Rol, todas las entidades afectadas.
- **Evidencia:** Todos los archivos `controlador/c_*.php` (por ejemplo `c_agregarUsuario.php`, `c_bajaUsuario.php`, `c_altaAgente.php`, `c_cambiarPass.php`): ninguno verifica `$_SESSION['rol']`.
- **Certeza:** Explícita (ausencia verificada en el 100 % de los controladores). Es un **defecto de seguridad**, no una regla de negocio deseada. Ver Q-002.

---

# C. Usuarios

## BR-014 — Datos obligatorios y opcionales del alta de usuario
- **Descripción:** Para crear un usuario se exigen rol, legajo, nombre, apellido, nombre de usuario y contraseña; el correo es opcional.
- **Condición:** Envío del modal "Agregar Nuevo Usuario".
- **Comportamiento esperado:** El formulario marca `required` en rol, legajo, nombre, apellido, usuario y contraseña; el correo no es obligatorio. Si no se envía correo, se guarda cadena vacía.
- **Entidades:** Usuario, Rol.
- **Evidencia:** `headerNav.php` (378-421); `controlador/c_agregarUsuario.php` (6-15).
- **Certeza:** Explícita (validación de cliente; el servidor no revalida).

## BR-015 — El legajo identifica al usuario y se sugiere entre 1000 y 19999
- **Descripción:** El legajo es la clave primaria del usuario. En el alta se restringe en el navegador al rango 1000–19999.
- **Condición:** Alta de usuario.
- **Comportamiento esperado:** `input type="number" min="1000" max="19999" required`; la base rechaza legajos duplicados por PK.
- **Entidades:** Usuario.
- **Evidencia:** `headerNav.php` (393); `mantenimiento.sql` (101, 110).
- **Certeza:** Explícita para la PK; **inferida** como regla de negocio para el rango (sólo validación de cliente, no documentada).

## BR-016 — El nombre de usuario debe ser único
- **Descripción:** El sistema advierte si el nombre de usuario ya existe y deshabilita el botón de guardado mientras el duplicado persista.
- **Condición:** Escritura en el campo "Nombre de Usuario" del alta.
- **Comportamiento esperado:** Llamada AJAX a `validarUsernameExistente.php`; si existe, muestra "Este nombre de usuario ya existe" y deshabilita el envío.
- **Entidades:** Usuario.
- **Evidencia:** `headerNav.php` (85-94, 414-416); `validacionDatosRepetidos/validarUsernameExistente.php`.
- **Certeza:** Explícita como validación de interfaz; **no garantizada** por el esquema ni por el servidor (no hay índice único en `usuario.usuario`). Ver Q-013.

## BR-017 — El alta de usuario persiste la contraseña hasheada
- **Descripción:** Al crear un usuario se almacena el hash de la contraseña, nunca el texto plano.
- **Condición:** Alta de usuario.
- **Comportamiento esperado:** `password_hash($pass, PASSWORD_DEFAULT)` antes del `INSERT`.
- **Entidades:** Usuario.
- **Evidencia:** `m_consultas.php::agregarUsuario` (118-121).
- **Certeza:** Explícita.

## BR-018 — Baja lógica de usuario con motivo
- **Descripción:** Dar de baja a un usuario no elimina la fila: se pone `idRol2 = NULL` y se guarda el motivo. El motivo es obligatorio.
- **Condición:** Acción "Dar de Baja" desde el listado de usuarios.
- **Comportamiento esperado:** `UPDATE usuario SET idRol2 = NULL, motivoBaja = '<motivo>' WHERE legajo = '<legajo>'`; alerta "El agente ha sido dado de baja".
- **Entidades:** Usuario, Rol.
- **Evidencia:** `usuariosCargados.php` (133-150); `controlador/c_bajaUsuario.php`; `m_consultas.php::bajaUsuario` (251-261).
- **Certeza:** Explícita.

## BR-019 — Reactivación (alta) de usuario con asignación de rol
- **Descripción:** Un usuario dado de baja puede reactivarse eligiendo un rol; se limpia el motivo de baja.
- **Condición:** Acción "Dar de alta" en el listado de bajas.
- **Comportamiento esperado:** `UPDATE usuario SET idRol2 = '<rol>', motivoBaja = NULL WHERE legajo = '<legajo>'`; alerta "El agente ha sido dado de alta al sistema nuevamente".
- **Entidades:** Usuario, Rol.
- **Evidencia:** `listarUsuariosBaja.php` (103-130); `controlador/c_altaAgente.php`; `m_consultas.php::altaUsuario` (263-273).
- **Certeza:** Explícita.

## BR-020 — El Administrador puede blanquear la contraseña de cualquier usuario
- **Descripción:** El Administrador selecciona un usuario y le asigna una nueva contraseña, que se guarda hasheada.
- **Condición:** Acción "Blanquear contraseña" con un legajo y una nueva contraseña.
- **Comportamiento esperado:** `UPDATE usuario SET contraseña = password_hash(<valor>) WHERE legajo = '<legajo>'`; alerta "La contraseña ha sido modificada".
- **Entidades:** Usuario.
- **Evidencia:** `blanqueoPass.php` (50-82); `controlador/c_cambiarPass.php`; `m_consultas.php::blanquearPass` (238-249).
- **Certeza:** Explícita.
- **Observación:** El controlador lee `$_POST['pass']` (campo de repetición) en lugar de `nuevaPass`; el efecto práctico es equivalente si el JS valida la coincidencia. Ver Q-009.

## BR-021 — Autogestión del perfil propio
- **Descripción:** Cualquier usuario autenticado puede modificar sus propios datos (nombre, apellido, correo, nombre de usuario y legajo) y, opcionalmente, su contraseña. No puede cambiar su rol.
- **Condición:** Menú de usuario → "Gestionar usuario actual" → Guardar.
- **Comportamiento esperado:** `UPDATE usuario SET legajo, nombre, apellido, correo, usuario [, contraseña] WHERE usuario = '<userAnterior>'`; si la contraseña se deja vacía, no se modifica. El campo `rol` se envía pero no se procesa. Al finalizar, se redirige al panel con "Los datos han sido modificados".
- **Entidades:** Usuario.
- **Evidencia:** `headerNav.php` (121-195); `controlador/c_editarUsuario.php`; `m_consultas.php::editarDatosUsuario` (207-224).
- **Certeza:** Explícita.
- **Observación:** Si el login se hizo con correo, `userAnterior` es el correo y el `UPDATE` no afecta filas, aunque el sistema informa éxito (ver Q-006).

## BR-022 — El rol no se modifica desde la autogestión
- **Descripción:** La autogestión no permite cambiar el rol; el campo oculto `rol` se recibe pero no se usa en la sentencia.
- **Condición:** Edición del propio perfil.
- **Comportamiento esperado:** El rol permanece igual; sólo el Administrador puede cambiarlo, y únicamente a través del alta/baja (no hay edición directa de rol).
- **Entidades:** Usuario, Rol.
- **Evidencia:** `controlador/c_editarUsuario.php` (12) — `$rol` se asigna y nunca se usa; `m_consultas.php::editarDatosUsuario`.
- **Certeza:** Explícita.

## BR-023 — Los usuarios de baja se excluyen de listados y conteos por rol, pero no del total
- **Descripción:** Los usuarios con `idRol2 IS NULL` no aparecen en el listado general de usuarios ni en los contadores por rol, pero sí se suman al "Total de Usuarios" del panel y aparecen en el listado específico de bajas.
- **Condición:** Consultas de listado y conteo.
- **Comportamiento esperado:** `listarUsuariosCargados` y `listarUsuarios` usan inner join con `tipousuario` (excluyen `NULL`); `contarUsuariosRol` filtra por rol (excluye `NULL`); `contarTotalUsuarios` cuenta `legajo != 0` (los incluye); `listarBajasUsuarios` filtra `idRol2 IS NULL`.
- **Entidades:** Usuario, Rol.
- **Evidencia:** `m_consultas.php` (59-77, 97-113, 604-632); `inicio.php` (48-50).
- **Certeza:** Explícita.

---

# D. Tareas / requerimientos

## BR-024 — Datos obligatorios y opcionales al crear una tarea
- **Descripción:** Para crear una tarea se exigen área, descripción y dirección/entidad. La nota electrónica es opcional, pero si se marca "Con Nota Electrónica" el campo se vuelve obligatorio en la interfaz. Nombre del afectado y celular son opcionales.
- **Condición:** Envío del modal "Nueva Tarea".
- **Comportamiento esperado:** El formulario aplica `required` a área, descripción y dirección; muestra/oculta la nota electrónica y alterna su `required`; afectado y celular no son obligatorios.
- **Entidades:** Tarea, Área, Dirección.
- **Evidencia:** `listarTareasAdmin.php` (162-219, 77-86); `controlador/c_agregarTarea.php`.
- **Certeza:** Explícita (validación de cliente; el servidor no revalida).

## BR-025 — Toda tarea nace en estado Pendiente con marcas temporales
- **Descripción:** Al crear una tarea se fija el estado `Pendiente` (id 1), `fechaProblema = NOW()`, `fechaCreada = CURDATE()` y `usuarioCreado` con el nombre completo del usuario de sesión.
- **Condición:** Alta de tarea.
- **Comportamiento esperado:** `INSERT` con `estadoTarea_id = '1'`, `fechaProblema = NOW()`, `fechaCreada = curdate()` y `usuarioCreado = '<nombreApellido de sesión>'`.
- **Entidades:** Tarea, EstadoTarea, Usuario.
- **Evidencia:** `m_consultas.php::agregarTarea` (396-407); `c_agregarTarea.php` (19); `listarTareasAdmin.php` (160).
- **Certeza:** Explícita.

## BR-026 — Todas las tareas son visibles para todos los usuarios autenticados
- **Descripción:** No existe aislamiento por creador, área, dirección ni responsable: cualquier usuario con rol 3 o 4 ve y opera todas las tareas del sistema.
- **Condición:** Acceso a cualquier listado de tareas.
- **Comportamiento esperado:** Las consultas de listado no filtran por usuario ni por rol; sólo por estado.
- **Entidades:** Tarea, Usuario.
- **Evidencia:** `m_consultas.php::listarTareasAdmin` (315-350), `::listarTareasEliminadas` (374-394); `headerNav.php` (292-305) sin condición de rol.
- **Certeza:** Explícita. Ver Q-005.

## BR-027 — Iniciar tarea: Pendiente → En Progreso
- **Descripción:** Una tarea Pendiente puede pasar a En Progreso mediante la acción "Iniciar la tarea". No se registra responsable ni fecha de inicio.
- **Condición:** Tarea en estado `Pendiente`; acción "Iniciar la tarea".
- **Comportamiento esperado:** `UPDATE tareas SET estadoTarea_id = '2' WHERE nroArreglo = '<id>'`; alerta "La tarea ha sido iniciada".
- **Entidades:** Tarea, EstadoTarea.
- **Evidencia:** `listarTareasAdmin.php` (533-541); `controlador/c_iniciarTarea.php`; `m_consultas.php::iniciarTarea` (423-433).
- **Certeza:** Explícita para la transición y la interfaz; la ausencia de responsable es explícita en el esquema (no hay columna).

## BR-028 — Terminar tarea: En Progreso → Completo, con solución, comprobante y fecha
- **Descripción:** Una tarea En Progreso puede marcarse como Completa indicando los materiales/solución y adjuntando un comprobante PDF. Se registra la fecha de solución.
- **Condición:** Tarea en estado `En Progreso`; acción "Terminar tarea"; formulario con `solucion` y archivo `comprobante`.
- **Comportamiento esperado:** Se sube el archivo a `../comprobantes/`, se guarda su ruta y se ejecuta `UPDATE ... SET solucion, comprobante, fechaSolucion = now(), estadoTarea_id = 3`.
- **Entidades:** Tarea, EstadoTarea, Comprobante.
- **Evidencia:** `listarTareasAdmin.php` (543-549, 851-888); `controlador/c_terminarTarea.php`; `m_consultas.php::terminarTarea` (435-446).
- **Certeza:** Explícita (obligatoriedad de materiales y PDF sólo en el HTML; no se revalida en el servidor).

## BR-029 — Cancelar tarea con motivo: → Cancelado
- **Descripción:** Una tarea Pendiente o En Progreso puede cancelarse indicando un motivo obligatorio. No se registra fecha de cancelación ni autor.
- **Condición:** Tarea cuyo estado no sea `Cancelado` ni `Completo` (en la práctica, Pendiente o En Progreso); acción "Cancelar tarea".
- **Comportamiento esperado:** `UPDATE tareas SET motivoCancelacion = '<motivo>', estadoTarea_id = 4 WHERE nroArreglo = '<id>'`; alerta "La tarea ha sido cancelada".
- **Entidades:** Tarea, EstadoTarea.
- **Evidencia:** `listarTareasAdmin.php` (553-560, 739-770); `controlador/c_cancelarTarea.php`; `m_consultas.php::cancelarTarea` (448-458).
- **Certeza:** Explícita para el comportamiento; **inferida** para el conjunto exacto de estados de origen (la UI lo condiciona, el servidor no).

## BR-030 — Eliminar tarea es un borrado lógico con motivo y fecha
- **Descripción:** Eliminar una tarea no borra la fila: la pasa a estado `Eliminado` (5), guarda el motivo y la fecha de eliminación. La tarea deja de aparecer en los listados operativos y pasa al listado de eliminadas.
- **Condición:** Acción "Eliminar tarea" con motivo obligatorio.
- **Comportamiento esperado:** `UPDATE tareas SET estadoTarea_id = 5, motivoEliminacion = '<motivo>', fechaEliminado = NOW() WHERE nroArreglo = '<id>'`; alerta "La tarea ha sido eliminada"; redirección al listado de eliminadas.
- **Entidades:** Tarea, EstadoTarea.
- **Evidencia:** `controlador/c_eliminarTarea.php`; `m_consultas.php::eliminarTarea` (460-470); `listaTareasEliminadas.php`.
- **Certeza:** Explícita.

## BR-031 — La edición de la tarea se limita a Pendiente y En Progreso
- **Descripción:** Sólo se ofrece editar tareas en estado Pendiente o En Progreso. Las tareas Completadas y Canceladas no muestran la opción.
- **Condición:** Estado de la tarea distinto de `Cancelado` y `Completo` en el listado principal.
- **Comportamiento esperado:** El ítem "Editar tarea" se renderiza sólo bajo esa condición.
- **Entidades:** Tarea, EstadoTarea.
- **Evidencia:** `listarTareasAdmin.php` (553-568).
- **Certeza:** Explícita en la interfaz; **no aplicada** en `c_editarTarea.php`.

## BR-032 — Transiciones de estado permitidas
- **Descripción:** El ciclo de vida admite únicamente estas transiciones:
  - `Pendiente → En Progreso` (iniciar)
  - `Pendiente → Cancelado` (cancelar)
  - `En Progreso → Completo` (terminar)
  - `En Progreso → Cancelado` (cancelar)
  - `Pendiente | En Progreso | Completo → Eliminado` (eliminar)
- **Condición:** Acciones disponibles según el estado actual.
- **Comportamiento esperado:** La interfaz ofrece sólo las acciones válidas para cada estado. No existe reapertura, restauración ni paso de Completo a En Progreso.
- **Entidades:** Tarea, EstadoTarea.
- **Evidencia:** `listarTareasAdmin.php` (533-574, 237-450); `estadotarea` (mantenimiento.sql 50-55).
- **Certeza:** **Inferida**: las transiciones se deducen de la combinación de acciones visibles. El backend no valida el estado de origen, por lo que técnicamente un `UPDATE` directo puede forzar cualquier combinación. Ver Q-008.

## BR-033 — No existe reapertura ni restauración
- **Descripción:** Una tarea Cancelada, Completa o Eliminada no puede volver a un estado anterior, ni restaurarse desde el listado de eliminadas.
- **Condición:** Tarea en estado 3, 4 o 5.
- **Comportamiento esperado:** No hay acción de UI ni sentencia que devuelva una tarea a estados 1 o 2. El listado de eliminadas sólo permite "Ver más info".
- **Entidades:** Tarea, EstadoTarea.
- **Evidencia:** `listaTareasEliminadas.php` (81-94); ausencia de métodos en `m_consultas.php`.
- **Certeza:** Explícita (por ausencia verificada de código).

## BR-034 — Listado "actual" = Pendiente + En Progreso
- **Descripción:** La opción "Tareas pendientes y En progreso" muestra las tareas en estado 1 y 2.
- **Condición:** `index.php?accion=listarTareasAdmin&lista=actual`.
- **Comportamiento esperado:** `... WHERE (estadoTarea_id = 1 OR estadoTarea_id = 2)`.
- **Entidades:** Tarea, EstadoTarea.
- **Evidencia:** `m_consultas.php::listarTareasAdmin` (319-324); `headerNav.php` (294).
- **Certeza:** Explícita.

## BR-035 — Listado de completadas, con sublistado de completadas hoy
- **Descripción:** La opción "Tareas completadas" muestra todas las tareas en estado 3 y, además, una tabla superior con las completadas en la fecha actual (`date(fechaSolucion) = CURDATE()`).
- **Condición:** `index.php?accion=listarTareasAdmin&lista=completas`.
- **Comportamiento esperado:** Dos consultas: estado 3 en general y estado 3 con fecha de solución de hoy.
- **Entidades:** Tarea, EstadoTarea.
- **Evidencia:** `m_consultas.php::listarTareasAdmin` (325-330), `::listarTareasCompletasActual` (352-372); `c_admin.php` (86-89).
- **Certeza:** Explícita.

## BR-036 — Listado de canceladas
- **Descripción:** La opción "Tareas canceladas" muestra las tareas en estado 4, sin acciones disponibles.
- **Condición:** `index.php?accion=listarTareasAdmin&lista=canceladas`.
- **Comportamiento esperado:** Filtro `estadoTarea_id = 4`; tabla sólo de lectura.
- **Entidades:** Tarea, EstadoTarea.
- **Evidencia:** `m_consultas.php::listarTareasAdmin` (331-336); `listarTareasAdmin.php` (468-577).
- **Certeza:** Explícita.

## BR-037 — Listado de eliminadas
- **Descripción:** La opción "Tareas eliminadas" muestra las tareas en estado 5 con el motivo y la fecha de eliminación.
- **Condición:** `index.php?accion=listarTareasEliminadas`.
- **Comportamiento esperado:** Filtro `estadoTarea_id = 5`; columnas #, Descripción, Afectado, Fecha Eliminada, Dirección, Motivo de Eliminación; acción "Ver más info".
- **Entidades:** Tarea, EstadoTarea.
- **Evidencia:** `m_consultas.php::listarTareasEliminadas` (374-394); `listaTareasEliminadas.php`.
- **Certeza:** Explícita.

## BR-038 — La nota electrónica es opcional
- **Descripción:** La nota electrónica es un dato opcional de la tarea. En los listados, si está vacía se muestra "No proporcionado".
- **Condición:** Alta o edición de tarea; visualización del detalle.
- **Comportamiento esperado:** Se permite dejar `nota_electronica` en `NULL`; las vistas sustituyen vacío por "No proporcionado".
- **Entidades:** Tarea.
- **Evidencia:** `listarTareasAdmin.php` (183-193, 327-337); `mantenimiento.sql` (61).
- **Certeza:** Explícita como intención; **pendiente de confirmar** en la práctica porque el alta y la edición nunca la persisten correctamente (ver Q-007).

## BR-039 — El comprobante es un archivo PDF por tarea
- **Descripción:** Al completar una tarea se adjunta un comprobante en PDF. El archivo se guarda en disco y la base sólo conserva la ruta. Hay como máximo un comprobante por tarea.
- **Condición:** Terminar una tarea.
- **Comportamiento esperado:** El archivo se mueve a `../comprobantes/`; se guarda la ruta en `tareas.comprobante`; el detalle permite descargarlo.
- **Entidades:** Tarea, Comprobante.
- **Evidencia:** `controlador/c_terminarTarea.php` (8-15); `listarTareasAdmin.php` (370-372, 873-877).
- **Certeza:** Explícita. El "sólo PDF" es una restricción de cliente (`accept=".pdf"`), no de servidor.

## BR-040 — La tarea se vincula al creador por su nombre completo
- **Descripción:** El creador de una tarea se almacena como texto `"Nombre Apellido"` en `tareas.usuarioCreado`; los listados recuperan el legajo del creador uniendo esa cadena con `CONCAT(usuario.nombre, ' ', usuario.apellido)`.
- **Condición:** Alta de tarea y consultas de listado.
- **Comportamiento esperado:** El alta escribe el nombre de sesión; el listado hace `t.usuarioCreado = CONCAT(u.nombre, ' ', u.apellido)` y muestra el legajo.
- **Entidades:** Tarea, Usuario.
- **Evidencia:** `listarTareasAdmin.php` (160); `m_consultas.php::agregarTarea` (401), `::listarTareasAdmin` (323, 329, 335), `::listarTareasCompletasActual` (359), `::listarTareasEliminadas` (381).
- **Certeza:** Explícita.
- **Consecuencia:** La integridad depende de que el nombre no cambie y de que el usuario siga teniendo rol. Ver BR-041.

## BR-041 — Las tareas desaparecen de los listados si el creador se da de baja o cambia de nombre
- **Descripción:** Como la unión con `usuario` es un inner join por nombre completo, una tarea cuyo creador fue dado de baja (o renombrado) deja de aparecer en los listados operativos, de completadas y de eliminadas.
- **Condición:** `idRol2 IS NULL` del creador, o cambio de `nombre`/`apellido` después de crear la tarea.
- **Comportamiento esperado (actual):** La fila de `tareas` sigue existiendo y cuenta en las estadísticas, pero no se muestra en los listados.
- **Entidades:** Tarea, Usuario.
- **Evidencia:** `m_consultas.php` (323, 329, 335, 359, 381) — todas las consultas de listado incluyen `usuario` con inner join implícito y comparación por `CONCAT`.
- **Certeza:** **Inferida** (consecuencia verificable del SQL; no hay intención documentada). Es un defecto funcional. Ver Q-014.

## BR-042 — Editar una tarea no altera su estado, fechas, solución ni creador
- **Descripción:** La edición sólo modifica descripción, nota electrónica, afectado, celular, área y dirección. No cambia estado, fechas, solución, comprobante ni autor.
- **Condición:** Edición de tarea.
- **Comportamiento esperado:** El `UPDATE` incluye únicamente `nombreApellidoAfectado`, `celular`, `descripcion`, `nota_electronica`, `direccion_codigo` y `codigoArea3`.
- **Entidades:** Tarea.
- **Evidencia:** `m_consultas.php::editarTarea` (409-421).
- **Certeza:** Explícita.

---

# E. Catálogos: áreas y direcciones

## BR-043 — El área es obligatoria en toda tarea
- **Descripción:** Toda tarea debe estar asociada a un área del catálogo.
- **Condición:** Alta o edición de tarea.
- **Comportamiento esperado:** El formulario exige `selectArea`; la base exige la FK `codigoArea3 → areas.codigo`.
- **Entidades:** Tarea, Área.
- **Evidencia:** `listarTareasAdmin.php` (162-175); `mantenimiento.sql` (80).
- **Certeza:** Explícita.

## BR-044 — La dirección/entidad es obligatoria en toda tarea
- **Descripción:** Toda tarea debe estar asociada a una dirección o entidad del catálogo.
- **Condición:** Alta o edición de tarea.
- **Comportamiento esperado:** El formulario exige `direccion` / `selectDireccion`; la base exige la FK `direccion_codigo → direcciones.codigo`.
- **Entidades:** Tarea, Dirección.
- **Evidencia:** `listarTareasAdmin.php` (205-219); `mantenimiento.sql` (81).
- **Certeza:** Explícita.

## BR-045 — El código de dirección es manual y único
- **Descripción:** El código de una dirección/entidad no es autoincremental: lo ingresa el administrador y debe ser único. La interfaz avisa por AJAX si ya existe.
- **Condición:** Alta o edición de dirección.
- **Comportamiento esperado:** `input` de código; validación AJAX contra `direcciones`; la PK impide duplicados.
- **Entidades:** Dirección.
- **Evidencia:** `listaDirecciones.php` (90-95, 178-181); `validacionDatosRepetidos/validarAreaExistente.php`; `mantenimiento.sql` (34).
- **Certeza:** Explícita para la unicidad; **inferida** para el formato (no hay validación de rango ni de tipo en el servidor).

## BR-046 — Una dirección con tareas asociadas no puede eliminarse
- **Descripción:** La eliminación de direcciones está restringida por la clave foránea: si existen tareas que la referencian, el borrado falla.
- **Condición:** Intento de `DELETE` sobre una dirección referenciada.
- **Comportamiento esperado:** La base rechaza la operación (RESTRICT por defecto, sin `ON DELETE`). El controlador no informa el error.
- **Entidades:** Dirección, Tarea.
- **Evidencia:** `mantenimiento.sql` (81); `m_consultas.php::eliminarDireccion` (560-570); `controlador/c_eliminarDireccion.php`.
- **Certeza:** **Inferida** (consecuencia del esquema y del motor InnoDB; no hay manejo explícito).

## BR-047 — Las áreas no se eliminan
- **Descripción:** No existe operación de baja ni de eliminación de áreas; sólo alta y edición.
- **Condición:** Uso del módulo de áreas.
- **Comportamiento esperado:** La interfaz sólo ofrece "Nueva área" y "Editar"; no hay endpoint de borrado.
- **Entidades:** Área.
- **Evidencia:** `listaAreas.php`; `m_consultas.php` (sólo `agregarNuevoArea` y `editarArea`).
- **Certeza:** Explícita (por ausencia verificada).

---

# F. Estadísticas y reportes

## BR-048 — El panel muestra totales globales por estado y total de tareas
- **Descripción:** El panel de control muestra el total de tareas del sistema (incluidas las eliminadas) y un contador por cada estado, además de la cantidad de usuarios (total, administradores y administrativos) y de direcciones.
- **Condición:** Acceso al panel tras el login.
- **Comportamiento esperado:** Conteos globales sin filtro de fecha; las tarjetas de estado enlazan a los listados correspondientes.
- **Entidades:** Tarea, EstadoTarea, Usuario, Rol, Dirección.
- **Evidencia:** `inicio.php` (36-53, 55-168); `m_consultas.php` (574-632); `m_estadisticas.php::contarTareasTotal` (166-179).
- **Certeza:** Explícita.

## BR-049 — Estadísticas por período según la fecha de creación
- **Descripción:** Las estadísticas se calculan para cuatro períodos: hoy, rango de fechas, año actual y total histórico. El criterio de fecha es `fechaCreada` de la tarea (excepto el conteo por estado "de hoy", ver BR-051).
- **Condición:** Selección de una opción del menú "Estadísticas".
- **Comportamiento esperado:**
  - Hoy: `fechaCreada = CURDATE()`.
  - Rango: `fechaCreada BETWEEN fechaInicio AND fechaFin`.
  - Año: `YEAR(fechaCreada) = YEAR(CURDATE())`.
  - Total: sin filtro.
- **Entidades:** Tarea, EstadoTarea, Área.
- **Evidencia:** `c_admin.php::verEstadisticasContr` (15-71); `m_estadisticas.php` (7-179).
- **Certeza:** Explícita.

## BR-050 — Desglose de estadísticas por área
- **Descripción:** Las estadísticas incluyen una tabla con la cantidad de tareas por área para el período seleccionado.
- **Condición:** Cualquier período de estadísticas.
- **Comportamiento esperado:** Se recorre el catálogo de áreas y se cuenta `tareas.codigoArea3 = areas.codigo` con el filtro de fecha correspondiente; se muestran nombre, descripción y cantidad.
- **Entidades:** Tarea, Área.
- **Evidencia:** `m_estadisticas.php` (39-54, 92-108, 146-162, 198-213); `estadisticas.php` (60-82).
- **Certeza:** Explícita.

## BR-051 — El conteo por estado "de hoy" usa la fecha del problema
- **Descripción:** En el período "hoy", el total de tareas se cuenta por `fechaCreada`, pero los totales por estado se cuentan por `fechaProblema`. Como `fechaProblema` es `DATETIME` y se compara con `CURDATE()` (fecha sin hora), la comparación casi nunca coincide y los conteos por estado quedan en cero.
- **Condición:** `time=now`.
- **Comportamiento esperado (actual):** `SELECT count(*) ... WHERE t.fechaProblema = curdate()` → en MySQL equivale a `fechaProblema = 'YYYY-MM-DD 00:00:00'`.
- **Entidades:** Tarea, EstadoTarea.
- **Evidencia:** `m_estadisticas.php::contarEstadoHoy` (22-37).
- **Certeza:** **Pendiente de confirmar** (defecto técnico verificable; se desconoce la intención). Ver Q-010.

## BR-052 — Exportación de listados a Excel y PDF
- **Descripción:** El listado principal de tareas permite exportar a Excel y PDF las columnas #, Nota Electrónica, Descripción, Afectado, Contacto, Estado, Fecha Problema, Dirección y Área.
- **Condición:** Uso de los botones de DataTables en el listado de tareas.
- **Comportamiento esperado:** Generación en el navegador con título "Listado de Tareas - Gestión de tareas" y sello de fecha/hora local.
- **Entidades:** Tarea.
- **Evidencia:** `listarTareasAdmin.php` (35-64); `libreriaEstilos.php` (19-25).
- **Certeza:** Explícita.

---

# G. Trazabilidad y otras reglas transversales

## BR-053 — No existe historial de estados ni de cambios
- **Descripción:** El sistema no guarda un historial de transiciones ni de ediciones. Sólo conserva el valor actual de cada campo y algunas marcas temporales.
- **Condición:** Cualquier operación sobre una tarea.
- **Comportamiento esperado:** Los cambios se aplican con `UPDATE` sobre la misma fila; no se insertan registros de auditoría.
- **Entidades:** Tarea, EstadoTarea.
- **Evidencia:** `m_consultas.php` (todas las operaciones de tarea); ausencia de tabla de historial en `mantenimiento.sql`.
- **Certeza:** Explícita (por ausencia verificada).

## BR-054 — Marcas temporales y autor conservadas
- **Descripción:** Por cada tarea se conservan: fecha de creación (`fechaCreada`), fecha/hora de problema (`fechaProblema`), fecha/hora de solución (`fechaSolucion`), fecha/hora de eliminación (`fechaEliminado`) y el nombre del creador (`usuarioCreado`). No se registran autores ni fechas de inicio, cancelación ni edición.
- **Condición:** Altas, terminaciones y eliminaciones.
- **Comportamiento esperado:** Las marcas se escriben en las operaciones correspondientes; el resto de los campos de fecha/autor no existe.
- **Entidades:** Tarea, Usuario.
- **Evidencia:** `m_consultas.php::agregarTarea` (401), `::terminarTarea` (439), `::eliminarTarea` (464); `mantenimiento.sql` (65-74).
- **Certeza:** Explícita.

---

## Anexo: reglas que **no** deben confundirse con reglas de negocio

Los siguientes comportamientos son **defectos o limitaciones de la implementación**, no reglas del negocio, y no deberían trasladarse a la nueva aplicación sin decisión humana:

| Comportamiento | Regla afectada |
|---|---|
| La nota electrónica nunca se guarda al crear / se borra al editar | BR-038 (ver Q-007) |
| El `UPDATE` del perfil falla silenciosamente si el login fue con correo | BR-021 (ver Q-006) |
| Los conteos por estado "de hoy" quedan en cero | BR-051 (ver Q-010) |
| La vista de estadísticas imprime `[0][0]` sobre un escalar | BR-049 (ver Q-011) |
| Las tareas desaparecen si el creador se da de baja o cambia de nombre | BR-040 / BR-041 (ver Q-014) |
| Los roles no se validan en el backend | BR-013 (ver Q-002) |
| El usuario de baja puede seguir iniciando sesión | BR-007 (ver Q-001) |
| La contraseña blanqueada se toma del campo de repetición | BR-020 (ver Q-009) |
