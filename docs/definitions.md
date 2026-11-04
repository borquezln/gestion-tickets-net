# Respuestas a incertidumbres y preguntas abiertas

> Es importante saber que este sistema gestiona tickets de un área operativa que necesita resolver problemas a toda la organización. Puede ser para sistemas o para mantenimiento, por ejemplo. Y puede ser ampliable para que ambas áreas operativas tengan acceso al mismo sistema, con sus tareas debidamente separadas según la pertenencia de cada usuario.

## Índice

| ID | Tema | Prioridad |
|---|---|---|
| Q-001 | Login de usuarios dados de baja | Bloqueante |
| Q-002 | Autorización real en el backend | Bloqueante |
| Q-003 | Catálogo de roles definitivo | Bloqueante |
| Q-004 | ¿El Administrativo puede crear/editar áreas? | Alta |
| Q-005 | Visibilidad de tareas entre usuarios | Bloqueante |
| Q-006 | Login por correo vs autogestión del perfil | Alta |
| Q-007 | Nota electrónica: ¿existe y debe persistirse? | Alta |
| Q-008 | Validación de transiciones de estado en el servidor | Alta |
| Q-009 | Campo usado en el blanqueo de contraseña | Media |
| Q-010 | Estadísticas "de hoy" por estado | Alta |
| Q-011 | Render de la tabla de estadísticas | Media |
| Q-012 | Política de expiración de sesión | Media |
| Q-013 | Unicidad de usuario y correo | Alta |
| Q-014 | Tareas que desaparecen al dar de baja/renombrar al creador | Bloqueante |
| Q-015 | Asignación de tareas / concepto de "agente" | Bloqueante |
| Q-016 | Eliminación desde cualquier estado y ausencia de restauración | Alta |
| Q-017 | Historial y trazabilidad | Alta |
| Q-018 | Direcciones: borrado y cambio de código | Media |
| Q-019 | Comprobantes: validación, almacenamiento y acceso | Alta |
| Q-020 | Criterio de fecha y alcance de las estadísticas | Alta |
| Q-021 | Migración de datos históricos | Bloqueante |
| Q-022 | Restos de funcionalidad en el modal de tareas | Media |
| Q-023 | `fechaProblema` vs `fechaCreada` | Alta |
| Q-024 | Cambio de rol de un usuario | Alta |
| Q-025 | Archivos huérfanos: ¿eliminar o recuperar? | Media |
| Q-026 | Zona horaria y formato de fechas | Media |

---

## Q-001 — ¿Un usuario dado de baja debe poder iniciar sesión?

- **Contexto:** La baja sólo pone `idRol2 = NULL` y guarda el motivo. El login no consulta el rol ni el estado.
- **Elección:**
  * La baja debe impedir el login y además invalidar sesiones activas y/o la contraseña.

## Q-002 — ¿La autorización debe aplicarse en el backend?

- **Contexto:** Todos los controladores ejecutan operaciones sin verificar sesión ni rol. Las restricciones son sólo visuales.
- **Elección:**
  * La nueva implementación debe aplicar autorización por rol en todos los endpoints.

## Q-003 — ¿Cuáles son los roles definitivos del negocio?

- **Contexto:** El esquema sólo contiene los roles 3 ("Administrador") y 4 ("Administrativo"). Los identificadores 3 y 4 sugieren roles 1 y 2 previos. La columna `idRol2` también sugiere un esquema anterior.
- **Elección:**
  * Existen Administrador y Administrativo, pero se prevén nuevos roles (como agente y responsable de área). Se pueden reiniciar los índices.

## Q-004 — ¿El rol Administrativo debe poder crear y editar áreas?

- **Contexto:** El módulo de áreas no tiene ningún control de rol, a diferencia de direcciones y usuarios. No hay evidencia de que sea intencional.
- **Elección:**
  * Sólo el Administrador debe administrar áreas, como sucede con direcciones y usuarios.
- **Observación:** Un área debe ser una dependencia de una dirección.

## Q-005 — ¿Las tareas deben ser visibles para todos los usuarios?

- **Contexto:** No hay filtro por creador, área ni responsable; cualquier usuario autenticado ve y opera todas las tareas.
- **Elección:**
  * Visibilidad por rol, a definir cuando se creen los roles nuevos.

## Q-006 — ¿Cómo debe funcionar el login por correo respecto de la autogestión?

- **Contexto:** El login acepta usuario o correo y guarda en `$_SESSION['username']` el texto ingresado. La autogestión usa ese valor como `userAnterior` en `WHERE usuario = '<valor>'`.
- **Elección:**
  * La autogestión debe localizar al usuario por legajo (de sesión).

## Q-007 — ¿Qué es la "Nota Electrónica" y debe persistirse?

- **Contexto:** Es un campo visible en el alta y la edición de tareas. El formulario lo envía como `ne`, pero los controladores leen `$_POST['nota_electronica']`, que nunca existe.
- **Elección:**
  * La Nota Electrónica es un documento que se inicializa en otro sistema de gestión administrativa. Se guarda el identificador para que el administrativo pueda revisarla por fuera de la gestión de tickets.

## Q-008 — ¿Debe el servidor validar las transiciones de estado?

- **Contexto:** La UI sólo muestra acciones válidas, pero `iniciarTarea`, `terminarTarea`, `cancelarTarea` y `editarTarea` ejecutan `UPDATE` sin comprobar el estado actual.
- **Elección:**
  * La máquina de estados es una regla de negocio y debe validarse en el dominio.

## Q-009 — ¿Qué contraseña se guarda en el blanqueo?

- **Contexto:** El formulario tiene `nuevaPass` (nueva contraseña) y `pass` (repetición). El controlador lee `$_POST['pass']`.
- **Elección:**
  * Usar `nuevaPass` y validar coincidencia también en servidor.

## Q-010 — ¿Cómo deben contarse las tareas "de hoy" por estado?

- **Contexto:** El total de tareas de hoy usa `fechaCreada = CURDATE()`, pero los conteos por estado usan `fechaProblema = CURDATE()`.
- **Elección:**
  * Unificar en `fechaProblema` y usar `DATE(fechaProblema) = CURDATE()` para "problemas reportados hoy".

## Q-011 — ¿La tabla de estadísticas debe mostrar los conteos?

- **Contexto:** Las funciones devuelven un escalar (`count(*)`), pero la vista imprime `$totalPendientes[0][0]`, etc.
- **Elección:**
  * La vista debe imprimir el escalar.

## Q-012 — ¿Cuál es la política de sesión esperada?

- **Contexto:** La expiración se controla por página con `$_SESSION['time']` y 3600 s fijos; además hay `<meta refresh content="3600">`.
- **Elección:**
  * Mantener un tiempo deslizante (a definir), centralizado en el backend.

## Q-013 — ¿Deben ser únicos el nombre de usuario y el correo?

- **Contexto:** Sólo hay validación AJAX para `usuario`; no hay índice único ni validación de `correo`. El login por correo asume unicidad.
- **Elección:**
  * Ambos deben ser únicos (con constraints en base).

## Q-014 — ¿Qué debe pasar con las tareas de un creador dado de baja o renombrado?

- **Contexto:** `tareas.usuarioCreado` guarda `"Nombre Apellido"`; los listados hacen inner join por `CONCAT(nombre, ' ', apellido)`.
- **Elección:**
  * Las tareas deben seguir visibles. Todas las tareas deben usar el legajo como vínculo.

## Q-015 — ¿Existe la asignación de tareas a un agente?

- **Contexto:** La interfaz habla de "agentes", hay mensajes de "tarea asignada" y archivos huérfanos que filtran "mis pendientes". Pero el esquema no tiene responsable y `iniciarTarea` sólo cambia el estado.
- **Elección:**
  * Debe existir en la nueva implementación (funcionalidad nueva, no migración).

## Q-016 — ¿Se puede eliminar una tarea desde cualquier estado? ¿Debe poder restaurarse?

- **Contexto:** El listado principal ofrece "Eliminar" en Pendiente y En Progreso; el listado de completadas también lo ofrece. No hay acción de restauración ni registro del estado previo.
- **Elección:**
  * Permitir eliminar desde cualquier estado. Permitir restaurar y conservar el estado previo.

## Q-017 — ¿Se requiere historial de cambios y trazabilidad?

- **Contexto:** No hay tabla de historial; sólo se conservan fechas sueltas y el nombre del creador. No se registra quién inició, completó, canceló o editó.
- **Elección:**
  * Incorporar historial de estados y auditoría de cambios (funcionalidad nueva).

## Q-018 — ¿Cómo deben gestionarse las direcciones: borrado y cambio de código?

- **Contexto:** Existe `c_eliminarDireccion.php` sin enlace en la UI; el borrado es físico y falla si hay tareas. La edición permite cambiar la PK.
- **Elección:**
  * Se debe incorporar una PK subrogada y mantener el código de una dirección como identificador que puede editarse, pero debe ser unique. Baja lógica (campo `active`) para preservar históricos.

## Q-019 — ¿Cómo deben almacenarse y servirse los comprobantes?

- **Contexto:** Se guardan en disco con el nombre original, sin validación ni metadatos, y se sirven por URL directa.
- **Elección:**
  * Mantener un archivo por tarea en almacenamiento local. Validar tipo/tamaño y servir mediante endpoint autenticado. Asignar un nombre relevante que ayude con la identificación.

## Q-020 — ¿Las estadísticas deben incluir tareas eliminadas? ¿Qué fecha define el período?

- **Contexto:** El total histórico incluye eliminadas; los períodos usan `fechaCreada`; el "hoy por estado" usa `fechaProblema`; las completadas de hoy usan `fechaSolucion`.
- **Elección:**
  * Definir KPIs por fecha de solución para "completadas" y por creación para "ingresadas". Tener la posibilidad de acceder a más estadísticas (eliminadas, canceladas, ingresadas; totales, por fecha y por rango).

## Q-021 — ¿Cómo se migrarán los datos históricos?

- **Contexto:** `tareas.usuarioCreado` es texto; hay usuarios de baja; hay posibles homónimos; los comprobantes están en disco; la codificación mezcla `utf8` y `utf8mb4`.
- **Preguntas concretas:**
  * No se migran los datos históricos. El sistema se inicializa de cero.

## Q-022 — ¿Qué hacer con los restos de funcionalidad del modal de tareas?

- **Contexto:** La vista menciona "asignación a agentes", adjuntos y una nota electrónica condicional; hay código huérfano que consulta columnas inexistentes.
- **Elección:**
  * Recuperar funcionalidades sobre las definiciones de este documento.

## Q-023 — ¿Qué significan `fechaProblema` y `fechaCreada`?

- **Contexto:** `fechaProblema` se llena con `NOW()` al crear, no con la fecha real del problema. `fechaCreada` es `CURDATE()`.
- **Elección:**
  * `fechaProblema` = fecha declarada del problema (campo editable). Dejar `fechaCreada` como timestamp del dato.

## Q-024 — ¿Cómo se cambia el rol de un usuario?

- **Contexto:** No hay pantalla de edición de usuarios de terceros. El rol sólo se asigna al crear y al reactivar. La autogestión envía `rol` pero no lo usa.
- **Elección:**
  * Agregar edición de rol para el Administrador.

## Q-025 — ¿Se eliminan los archivos huérfanos o se recuperan?

- **Contexto:** `listaAgentes.php`, `ajax/filtroEstado.php`, `ajax/filtroRadios.php`, `ajax/buscarPorFecha.php` no están referenciados y consultan columnas inexistentes.
- **Elección:**
  * Recuperar la funcionalidad de filtros/agentes teniendo en cuenta futuras definiciones.

## Q-026 — ¿Qué zona horaria y formato de fechas debe usar el sistema?

- **Contexto:** Las fechas se generan con `NOW()`/`CURDATE()` de MySQL y se muestran con `date_format(..., 'd/m/Y H:i:s')`. La base se creó con `TIME_ZONE='+00:00'` en el dump.
- **Elección:**
  * Zona horaria local fija (p. ej. America/Argentina/...).

---

## A. Contradicciones detectadas

| # | Contradicción | Resolución |
|---|---|---|
| C-01 | El nombre de la acción "Asignar tarea" / "Tarea asignada" sugiere asignación a un agente, pero no existe tal concepto en el modelo ni en la operación. | Crear funcionalidad para asignar tareas. |
| C-02 | La UI exige comprobante PDF para completar, pero el backend acepta cualquier archivo y no lo valida. | Agregar validación por backend. |
| C-03 | La UI exige una nota electrónica cuando el checkbox está marcado, pero el backend nunca la recibe. | Sanear el error y persistir Nota Electrónica. |
| C-04 | Las estadísticas "de hoy" se calculan con dos criterios distintos (`fechaCreada` vs `fechaProblema`). | Tomar las definiciones sobre la fecha para las estadísticas. |
| C-05 | La validación de código de dirección se ejecuta también al editar, donde el propio código siempre "existe". | Sanear la comprobación errónea. |
| C-06 | Los roles se restringen en la UI, pero no en el backend. | Restringir permisos por roles. |
| C-07 | El listado de tareas se une a usuarios por nombre, pero la baja o el renombre rompen la unión. | Unirlo por campos más representativos. |
| C-08 | `estadisticas.php` espera arreglos bidimensionales; el modelo devuelve escalares. | Recibir los escalares. |

## B. Funcionalidades aparentemente incompletas

| # | Funcionalidad | Estado | Resolución |
|---|---|---|---|
| I-01 | Eliminación de direcciones | Endpoint sin UI; sin manejo de error de FK. | Agregar con resctricción ON DELETE. |
| I-02 | Cambio de rol de usuario | Método `asignarRolUsuario` nunca invocado. | Agregar cambio de roles para el Administrador. |
| I-03 | Filtros AJAX de tareas | Endpoints con columnas inexistentes y sin referencias. | Refactorizar todo el AJAX al Frontend nuevo. |
| I-04 | Listado de agentes / baja de agente | Vista sin controlador y con variable no definida. | Se recupera al modelar el rol de Agente. |
| I-05 | Gráficos del panel | Chart.js cargado sin uso. | Refactorizar al Frontend nuevo. |
| I-06 | Adjuntos múltiples | Sólo hay un campo `comprobante`. | Un adjunto por tarea. |
| I-07 | Fecha de la barra lateral | Variable `$fechaActual` no inicializada en `headerNav.php`. | Inicializarla. |
| I-08 | Nota electrónica | No persiste (ver Q-007). | Asegurar la persistencia. |
| I-09 | Recuperación de contraseña | No existe; sólo blanqueo por administrador. | Mantener comportamiento actual. |
| I-10 | `c_eliminarTarea` desde el listado de canceladas | No hay menú de acciones en ese listado. | Agregar funcionalidad. |
| I-11 | Detalle de tarea eliminada | El modal imprime el estado en "Materiales utilizados" y la fecha del problema en "Motivo de la cancelación" (índices desfasados). | Arreglar la vista. |
