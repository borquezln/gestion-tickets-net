# Incertidumbres y preguntas abiertas

> Fase 0 — registro de comportamientos ambiguos, contradicciones y funcionalidades incompletas.
> **Ninguna de estas preguntas se resolvió por suposición.** Cada una requiere una decisión humana antes de avanzar a la implementación.
> Prioridad: **Bloqueante** (impide definir el modelo o los flujos), **Alta** (afecta comportamiento visible), **Media** (afecta calidad o datos históricos), **Baja** (detalle de implementación).

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
- **Evidencia:** `m_consultas.php::autenticarUsuario` (11-15); `::bajaUsuario` (251-261); `c_login.php`.
- **Comportamiento actual:** El usuario de baja puede autenticarse, obtiene `rol = NULL` y accede a `inicio.php`; las vistas con control de rol no le muestran opciones, pero los controladores de escritura no lo bloquean.
- **Opciones:**
  1. La baja debe impedir el login (validar `idRol2 IS NOT NULL`).
  2. La baja sólo quita permisos, pero permite el acceso en modo lectura.
  3. La baja debe además invalidar sesiones activas y/o la contraseña.
- **Impacto:** Modelo de autenticación, políticas de seguridad, flujo de alta/baja.
- **Decisión requerida antes de:** diseñar autenticación y autorización.
- **Prioridad:** Bloqueante.

## Q-002 — ¿La autorización debe aplicarse en el backend?

- **Contexto:** Todos los controladores ejecutan operaciones sin verificar sesión ni rol. Las restricciones son sólo visuales.
- **Evidencia:** `controlador/*.php` (18 archivos); `headerNav.php` (313-341); `listaDirecciones.php` (68, 124).
- **Comportamiento actual:** Cualquier cliente puede invocar endpoints de alta/baja de usuarios, blanqueo de contraseñas, etc.
- **Opciones:**
  1. Asumir que es un defecto y que la nueva implementación debe aplicar autorización por rol en todos los endpoints (recomendado como interpretación, pero **requiere confirmación**).
  2. Replicar el comportamiento actual (no recomendado; implicaría mantener el agujero).
- **Impacto:** Diseño de seguridad, matriz de permisos, pruebas.
- **Prioridad:** Bloqueante.

## Q-003 — ¿Cuáles son los roles definitivos del negocio?

- **Contexto:** El esquema sólo contiene los roles 3 ("Administrador") y 4 ("Administrativo"). Los identificadores 3 y 4 sugieren roles 1 y 2 previos. La columna `idRol2` también sugiere un esquema anterior.
- **Evidencia:** `mantenimiento.sql` (88-97); `headerNav.php` (235-247); `inicio.php` (49-50).
- **Comportamiento actual:** Sólo se distinguen dos roles; cualquier otro valor se trata como "no administrador".
- **Opciones:**
  1. Sólo existen Administrador y Administrativo.
  2. Existen roles históricos (1/2) que deben conservarse o migrarse.
  3. Se prevén nuevos roles (p. ej. agente técnico, supervisor) a futuro.
- **Impacto:** Modelo de roles, migración de datos, autorización.
- **Prioridad:** Bloqueante.

## Q-004 — ¿El rol Administrativo debe poder crear y editar áreas?

- **Contexto:** El módulo de áreas no tiene ningún control de rol, a diferencia de direcciones y usuarios. No hay evidencia de que sea intencional.
- **Evidencia:** `listaAreas.php` (50-52, 67-69) — sin condiciones; `headerNav.php` (307) — visible para ambos.
- **Comportamiento actual:** Ambos roles crean y editan áreas.
- **Opciones:**
  1. Es intencional: el Administrativo mantiene el catálogo de áreas.
  2. Es un olvido: sólo el Administrador debe administrar áreas.
- **Impacto:** Matriz de permisos y pruebas de autorización.
- **Prioridad:** Alta.

## Q-005 — ¿Las tareas deben ser visibles para todos los usuarios?

- **Contexto:** No hay filtro por creador, área ni responsable; cualquier usuario autenticado ve y opera todas las tareas.
- **Evidencia:** `m_consultas.php::listarTareasAdmin` (315-350); `headerNav.php` (292-305).
- **Comportamiento actual:** Visibilidad global.
- **Opciones:**
  1. Visibilidad global (comportamiento actual).
  2. Cada usuario ve sólo las tareas que creó.
  3. Visibilidad por área/dirección del usuario.
  4. Visibilidad por rol (p. ej. el Administrativo sólo opera, el Administrador ve todo).
- **Impacto:** Modelo de datos (asignación/ownership), consultas, seguridad.
- **Prioridad:** Bloqueante.

## Q-006 — ¿Cómo debe funcionar el login por correo respecto de la autogestión?

- **Contexto:** El login acepta usuario o correo y guarda en `$_SESSION['username']` el texto ingresado. La autogestión usa ese valor como `userAnterior` en `WHERE usuario = '<valor>'`.
- **Evidencia:** `c_login.php` (9); `headerNav.php` (123); `c_editarUsuario.php` (11); `m_consultas.php::editarDatosUsuario` (211-216).
- **Comportamiento actual:** Si el usuario inició sesión con su correo, el `UPDATE` no afecta filas; `mysqli_query` devuelve `true` y el sistema muestra "Los datos han sido modificados" sin haber cambiado nada. Lo mismo ocurre con `ultimoAcceso`.
- **Opciones:**
  1. Es un defecto: la autogestión debe localizar al usuario por legajo (de sesión) y no por el identificador ingresado.
  2. Es un defecto: el login por correo no debería existir.
  3. Mantener el comportamiento (no razonable, pero debe decidirse).
- **Impacto:** Autenticación, edición de perfil, auditoría.
- **Prioridad:** Alta.

## Q-007 — ¿Qué es la "Nota Electrónica" y debe persistirse?

- **Contexto:** Es un campo visible en el alta y la edición de tareas. El formulario lo envía como `ne`, pero los controladores leen `$_POST['nota_electronica']`, que nunca existe.
- **Evidencia:** `listarTareasAdmin.php` (190-193, 810-813); `c_agregarTarea.php` (14-18); `c_editarTarea.php` (9).
- **Comportamiento actual:**
  - Al crear, se guarda siempre `NULL`.
  - Al editar, se escribe cadena vacía/NULL y se **borra** cualquier valor previo.
  - La columna es `varchar(45)`, potencialmente insuficiente.
- **Opciones:**
  1. Es un dato funcional: corregir y persistir; definir longitud y formato.
  2. Es un dato obsoleto: eliminarlo de la interfaz y del modelo.
  3. Es un dato que debe migrarse de otra fuente (no hay evidencia de tal fuente).
- **Impacto:** Modelo de tarea, formularios, migración de datos.
- **Prioridad:** Alta.

## Q-008 — ¿Debe el servidor validar las transiciones de estado?

- **Contexto:** La UI sólo muestra acciones válidas, pero `iniciarTarea`, `terminarTarea`, `cancelarTarea` y `editarTarea` ejecutan `UPDATE` sin comprobar el estado actual.
- **Evidencia:** `m_consultas.php` (423-470); `c_*Tarea.php`.
- **Comportamiento actual:** Invocando el endpoint directamente se puede, por ejemplo, pasar una tarea Completa a En Progreso o editar una Cancelada.
- **Opciones:**
  1. La máquina de estados es una regla de negocio y debe validarse en el dominio.
  2. Es una restricción sólo de interfaz (poco defendible).
- **Impacto:** Modelo de dominio, pruebas, API.
- **Prioridad:** Alta.

## Q-009 — ¿Qué contraseña se guarda en el blanqueo?

- **Contexto:** El formulario tiene `nuevaPass` (nueva contraseña) y `pass` (repetición). El controlador lee `$_POST['pass']`.
- **Evidencia:** `blanqueoPass.php` (67-76); `c_cambiarPass.php` (7-8).
- **Comportamiento actual:** Se guarda el valor del campo de repetición. Como el JS impide enviar si no coinciden, el resultado práctico suele ser el mismo, pero la validación es eludible.
- **Opciones:**
  1. Es un defecto: usar `nuevaPass` y validar coincidencia también en servidor.
  2. Es intencional (no hay razón funcional aparente).
- **Impacto:** Seguridad y UX del blanqueo.
- **Prioridad:** Media.

## Q-010 — ¿Cómo deben contarse las tareas "de hoy" por estado?

- **Contexto:** El total de tareas de hoy usa `fechaCreada = CURDATE()`, pero los conteos por estado usan `fechaProblema = CURDATE()`.
- **Evidencia:** `m_estadisticas.php::contarTareasHoy` (11) vs `::contarEstadoHoy` (27).
- **Comportamiento actual:** `fechaProblema` es `DATETIME`; compararlo con una fecha equivale a comparar con medianoche, por lo que el conteo es casi siempre 0.
- **Opciones:**
  1. Unificar en `fechaCreada` (coherente con el resto de los períodos).
  2. Usar `DATE(fechaProblema) = CURDATE()` si la intención es "problemas reportados hoy".
  3. Usar `fechaSolucion` para "completadas hoy" y otro criterio para el resto.
- **Impacto:** Definición de KPIs y reportes.
- **Prioridad:** Alta.

## Q-011 — ¿La tabla de estadísticas debe mostrar los conteos?

- **Contexto:** Las funciones devuelven un escalar (`count(*)`), pero la vista imprime `$totalPendientes[0][0]`, etc.
- **Evidencia:** `m_estadisticas.php` (22-37, 74-90, 128-144, 181-196); `estadisticas.php` (39-55, 75).
- **Comportamiento actual:** En PHP, `$escalar[0]` devuelve el primer carácter de la cadena numérica y `[0][0]` su primer dígito: un conteo de 12 se muestra como "1". Si la consulta no devuelve filas, la celda queda vacía. Con `error_reporting(0)` los warnings quedan ocultos.
- **Opciones:**
  1. Es un defecto: la vista debe imprimir el escalar.
  2. La vista refleja un contrato anterior (funciones que devolvían matrices) y hay que decidir cuál es el correcto.
- **Impacto:** Reportes, pruebas.
- **Prioridad:** Media.

## Q-012 — ¿Cuál es la política de sesión esperada?

- **Contexto:** La expiración se controla por página con `$_SESSION['time']` y 3600 s fijos; además hay `<meta refresh content="3600">`.
- **Evidencia:** `index.php` (5-6, 67-69); `inicio.php` (12); `headerNav.php`.
- **Comportamiento actual:** Renovación deslizante en cada vista; las llamadas a controladores no la verifican; el `meta refresh` recarga toda la página cada hora.
- **Opciones:**
  1. Mantener 60 minutos deslizantes, pero centralizados en el backend.
  2. Expiración absoluta (p. ej. 8 horas) con renovación.
  3. Configurable por rol.
- **Impacto:** Seguridad, UX, implementación de la API.
- **Prioridad:** Media.

## Q-013 — ¿Deben ser únicos el nombre de usuario y el correo?

- **Contexto:** Sólo hay validación AJAX para `usuario`; no hay índice único ni validación de `correo`. El login por correo asume unicidad.
- **Evidencia:** `mantenimiento.sql` (99-113); `validarUsernameExistente.php`; `headerNav.php` (414).
- **Comportamiento actual:** Se pueden crear duplicados si se evita el JS o se llama al endpoint directamente. Un correo duplicado haría ambiguo el login.
- **Opciones:**
  1. Ambos deben ser únicos (con constraints en base).
  2. Sólo el usuario debe ser único; el correo puede repetirse (y entonces no usarse para login).
  3. Unicidad por organización/tenant (no evidenciado).
- **Impacto:** Modelo, migración de datos, autenticación.
- **Prioridad:** Alta.

## Q-014 — ¿Qué debe pasar con las tareas de un creador dado de baja o renombrado?

- **Contexto:** `tareas.usuarioCreado` guarda `"Nombre Apellido"`; los listados hacen inner join por `CONCAT(nombre, ' ', apellido)`.
- **Evidencia:** `m_consultas.php` (323, 329, 335, 359, 381); `::bajaUsuario`; `::editarDatosUsuario`.
- **Comportamiento actual:** Las tareas del creador desaparecen de los listados operativos (aunque siguen en la base y en las estadísticas).
- **Opciones:**
  1. Es un defecto: las tareas deben seguir visibles usando el legajo como vínculo.
  2. Es intencional: las tareas de usuarios de baja deben ocultarse (y entonces hay que definir qué pasa con las completadas y las eliminadas).
  3. Deben reasignarse a otro usuario al dar de baja.
- **Impacto:** Modelo (FK a usuario), migración de datos históricos, reportes.
- **Prioridad:** Bloqueante.

## Q-015 — ¿Existe la asignación de tareas a un agente?

- **Contexto:** La interfaz habla de "agentes", hay mensajes de "tarea asignada" y archivos huérfanos que filtran "mis pendientes". Pero el esquema no tiene responsable y `iniciarTarea` sólo cambia el estado.
- **Evidencia:** `m_consultas.php::iniciarTarea` (423-433); `listaAgentes.php`; `ajax/filtroRadios.php`; `listarTareasAdmin.php` (120-122); `c_iniciarTarea.php` (9).
- **Comportamiento actual:** No hay asignación. Cualquier usuario puede iniciar/terminar cualquier tarea.
- **Opciones:**
  1. No existe y no debe existir: el flujo es por estado y área.
  2. Debe existir en la nueva implementación (funcionalidad nueva, no migración).
  3. Existió en una versión anterior y hay que recuperarla.
- **Impacto:** Modelo de dominio, permisos, UI, reportes.
- **Prioridad:** Bloqueante.

## Q-016 — ¿Se puede eliminar una tarea desde cualquier estado? ¿Debe poder restaurarse?

- **Contexto:** El listado principal ofrece "Eliminar" en Pendiente y En Progreso; el listado de completadas también lo ofrece. No hay acción de restauración ni registro del estado previo.
- **Evidencia:** `listarTareasAdmin.php` (296-300, 570-574); `m_consultas.php::eliminarTarea`; `listaTareasEliminadas.php`.
- **Comportamiento actual:** Se puede eliminar una tarea Completa (y por endpoint una Cancelada). Se pierde el estado anterior.
- **Opciones:**
  1. Permitir eliminar desde cualquier estado (comportamiento actual) y no restaurar.
  2. Restringir la eliminación a ciertos estados.
  3. Permitir restaurar y conservar el estado previo.
  4. Reemplazar el borrado lógico por un archivado.
- **Impacto:** Modelo, auditoría, UI.
- **Prioridad:** Alta.

## Q-017 — ¿Se requiere historial de cambios y trazabilidad?

- **Contexto:** No hay tabla de historial; sólo se conservan fechas sueltas y el nombre del creador. No se registra quién inició, completó, canceló o editó.
- **Evidencia:** `mantenimiento.sql`; `m_consultas.php` (todas las operaciones de tarea).
- **Comportamiento actual:** Trazabilidad mínima.
- **Opciones:**
  1. Mantener la trazabilidad actual (sólo último estado + fechas).
  2. Incorporar historial de estados y auditoría de cambios (funcionalidad nueva).
  3. Incorporar sólo autor/fecha de cada transición.
- **Impacto:** Modelo, esfuerzo, reportes, requisitos legales.
- **Prioridad:** Alta.

## Q-018 — ¿Cómo deben gestionarse las direcciones: borrado y cambio de código?

- **Contexto:** Existe `c_eliminarDireccion.php` sin enlace en la UI; el borrado es físico y falla si hay tareas. La edición permite cambiar la PK.
- **Evidencia:** `c_eliminarDireccion.php`; `c_editarDireccion.php`; `m_consultas.php` (548-570); `mantenimiento.sql` (81).
- **Comportamiento actual:** Borrado físico no expuesto; cambio de código que puede fallar por FK; sin mensajes de error.
- **Opciones:**
  1. Sólo alta/edición, sin borrado (comportamiento visible).
  2. Baja lógica (campo `activo`) para preservar históricos.
  3. Borrado físico sólo si no tiene tareas, con mensaje claro.
  4. Código inmutable (no editable).
- **Impacto:** Modelo, integridad, UI.
- **Prioridad:** Media.

## Q-019 — ¿Cómo deben almacenarse y servirse los comprobantes?

- **Contexto:** Se guardan en disco con el nombre original, sin validación ni metadatos, y se sirven por URL directa.
- **Evidencia:** `c_terminarTarea.php` (8-15); `listarTareasAdmin.php` (370-372); `styles`/estructura del repo (no hay carpeta `comprobantes/` versionada).
- **Comportamiento actual:** Un PDF por tarea, sin control de acceso, con riesgo de sobrescritura.
- **Opciones:**
  1. Mantener un archivo por tarea en almacenamiento local.
  2. Almacenamiento de objetos (S3/Azure Blob) con metadatos.
  3. Permitir múltiples adjuntos.
  4. Validar tipo/tamaño y servir mediante endpoint autenticado.
- **Impacto:** Infraestructura, seguridad, migración de archivos.
- **Prioridad:** Alta.

## Q-020 — ¿Las estadísticas deben incluir tareas eliminadas? ¿Qué fecha define el período?

- **Contexto:** El total histórico incluye eliminadas; los períodos usan `fechaCreada`; el "hoy por estado" usa `fechaProblema`; las completadas de hoy usan `fechaSolucion`.
- **Evidencia:** `m_estadisticas.php`; `listarTareasCompletasActual` (360).
- **Comportamiento actual:** Criterios heterogéneos.
- **Opciones:**
  1. Todas las estadísticas por `fechaCreada`, excluyendo eliminadas de los totales operativos.
  2. Incluir eliminadas en un total histórico pero no en los períodos.
  3. Definir KPIs por fecha de solución para "completadas" y por creación para "ingresadas".
- **Impacto:** Definición de reportes y consultas.
- **Prioridad:** Alta.

## Q-021 — ¿Cómo se migrarán los datos históricos?

- **Contexto:** `tareas.usuarioCreado` es texto; hay usuarios de baja; hay posibles homónimos; los comprobantes están en disco; la codificación mezcla `utf8` y `utf8mb4`.
- **Evidencia:** `mantenimiento.sql`; `m_consultas.php`; `m_conexion.php` (11).
- **Comportamiento actual:** No hay script de migración ni identificadores estables más allá de `legajo` y `nroArreglo`.
- **Preguntas concretas:**
  1. ¿Se migran todas las tareas históricas o sólo un período?
  2. ¿Cómo se resuelven tareas cuyo `usuarioCreado` no coincide con ningún usuario (renombrados/homónimos)?
  3. ¿Se migran los comprobantes? ¿Con qué verificación?
  4. ¿Se conservan los ids (`nroArreglo`, `legajo`) como claves?
  5. ¿Qué pasa con usuarios de baja?
- **Impacto:** Estrategia de migración, esfuerzo, integridad.
- **Prioridad:** Bloqueante.

## Q-022 — ¿Qué hacer con los restos de funcionalidad del modal de tareas?

- **Contexto:** La vista menciona "asignación a agentes", adjuntos y una nota electrónica condicional; hay código huérfano que consulta columnas inexistentes.
- **Evidencia:** `listarTareasAdmin.php` (88, 140, 183-193); `ajax/*`; `listaAgentes.php`.
- **Comportamiento actual:** Son restos de una versión anterior; no funcionan.
- **Opciones:**
  1. Descartarlos por completo (no forman parte del negocio actual).
  2. Recuperar alguna de esas funcionalidades (requiere definición de producto).
- **Impacto:** Alcance del proyecto.
- **Prioridad:** Media.

## Q-023 — ¿Qué significan `fechaProblema` y `fechaCreada`?

- **Contexto:** `fechaProblema` se llena con `NOW()` al crear, no con la fecha real del problema. `fechaCreada` es `CURDATE()`.
- **Evidencia:** `m_consultas.php::agregarTarea` (400-401).
- **Comportamiento actual:** Ambas son casi la misma fecha (con/sin hora). El nombre de `fechaProblema` sugiere que debería permitir ingresar cuándo ocurrió el problema.
- **Opciones:**
  1. `fechaProblema` = momento de alta (comportamiento actual).
  2. `fechaProblema` = fecha declarada del problema (campo editable).
  3. Eliminar una de las dos.
- **Impacto:** Modelo, formularios, estadísticas.
- **Prioridad:** Alta.

## Q-024 — ¿Cómo se cambia el rol de un usuario?

- **Contexto:** No hay pantalla de edición de usuarios de terceros. El rol sólo se asigna al crear y al reactivar. La autogestión envía `rol` pero no lo usa.
- **Evidencia:** `c_altaAgente.php`; `c_editarUsuario.php` (12); `m_consultas.php::asignarRolUsuario` (226-236) — **método definido pero nunca invocado**.
- **Comportamiento actual:** Para cambiar el rol hay que dar de baja y volver a dar de alta.
- **Opciones:**
  1. Mantener el flujo actual (baja + alta).
  2. Agregar edición de rol para el Administrador (usando `asignarRolUsuario`).
  3. Eliminar el método muerto.
- **Impacto:** UI, permisos, auditoría.
- **Prioridad:** Alta.

## Q-025 — ¿Se eliminan los archivos huérfanos o se recuperan?

- **Contexto:** `listaAgentes.php`, `ajax/filtroEstado.php`, `ajax/filtroRadios.php`, `ajax/buscarPorFecha.php` no están referenciados y consultan columnas inexistentes.
- **Evidencia:** Búsqueda de referencias en todo el repositorio; ver `current-functionalities.md` §9.
- **Opciones:**
  1. No migrarlos (recomendado como análisis, pero requiere confirmación).
  2. Recuperar la funcionalidad de filtros/agentes (implica definirla).
- **Impacto:** Alcance.
- **Prioridad:** Media.

## Q-026 — ¿Qué zona horaria y formato de fechas debe usar el sistema?

- **Contexto:** Las fechas se generan con `NOW()`/`CURDATE()` de MySQL y se muestran con `date_format(..., 'd/m/Y H:i:s')`. La base se creó con `TIME_ZONE='+00:00'` en el dump.
- **Evidencia:** `mantenimiento.sql` (12); `m_consultas.php`; vistas.
- **Comportamiento actual:** Depende de la zona horaria del servidor MySQL y de PHP; no hay configuración explícita.
- **Opciones:**
  1. UTC en persistencia y conversión a zona local en el frontend.
  2. Zona horaria local fija (p. ej. America/Argentina/...).
- **Impacto:** Reportes, fechas de creación/solución, migración.
- **Prioridad:** Media.

---

## A. Contradicciones detectadas

| # | Contradicción | Evidencia |
|---|---|---|
| C-01 | El nombre de la acción "Asignar tarea" / "Tarea asignada" sugiere asignación a un agente, pero no existe tal concepto en el modelo ni en la operación. | `listarTareasAdmin.php` (120-122, 537-539); `m_consultas.php::iniciarTarea`. |
| C-02 | La UI exige comprobante PDF para completar, pero el backend acepta cualquier archivo y no lo valida. | `listarTareasAdmin.php` (876); `c_terminarTarea.php`. |
| C-03 | La UI exige una nota electrónica cuando el checkbox está marcado, pero el backend nunca la recibe. | `listarTareasAdmin.php` (77-86, 191); `c_agregarTarea.php` (14). |
| C-04 | Las estadísticas "de hoy" se calculan con dos criterios distintos (`fechaCreada` vs `fechaProblema`). | `m_estadisticas.php` (11, 27). |
| C-05 | La validación de código de dirección se ejecuta también al editar, donde el propio código siempre "existe". | `listaDirecciones.php` (179); `validarAreaExistente.php`. |
| C-06 | Los roles se restringen en la UI, pero no en el backend. | `headerNav.php` vs `controlador/*.php`. |
| C-07 | El listado de tareas se une a usuarios por nombre, pero la baja o el renombre rompen la unión. | `m_consultas.php` (323, 329, 335, 359, 381). |
| C-08 | `estadisticas.php` espera arreglos bidimensionales; el modelo devuelve escalares. | `estadisticas.php` (39-55); `m_estadisticas.php`. |

## B. Funcionalidades aparentemente incompletas

| # | Funcionalidad | Estado | Evidencia |
|---|---|---|---|
| I-01 | Eliminación de direcciones | Endpoint sin UI; sin manejo de error de FK. | `c_eliminarDireccion.php`. |
| I-02 | Cambio de rol de usuario | Método `asignarRolUsuario` nunca invocado. | `m_consultas.php` (226-236). |
| I-03 | Filtros AJAX de tareas | Endpoints con columnas inexistentes y sin referencias. | `vistaAgente/ajax/*`. |
| I-04 | Listado de agentes / baja de agente | Vista sin controlador y con variable no definida. | `listaAgentes.php`. |
| I-05 | Gráficos del panel | Chart.js cargado sin uso. | `inicio.php` (18). |
| I-06 | Adjuntos múltiples | Sólo hay un campo `comprobante`. | `listarTareasAdmin.php` (873-877). |
| I-07 | Fecha de la barra lateral | Variable `$fechaActual` no inicializada en `headerNav.php`. | `headerNav.php` (346-354). |
| I-08 | Nota electrónica | No persiste (ver Q-007). | `c_agregarTarea.php`, `c_editarTarea.php`. |
| I-09 | Recuperación de contraseña | No existe; sólo blanqueo por administrador. | — |
| I-10 | `c_eliminarTarea` desde el listado de canceladas | No hay menú de acciones en ese listado. | `listarTareasAdmin.php` (468-577). |
| I-11 | Detalle de tarea eliminada | El modal imprime el estado en "Materiales utilizados" y la fecha del problema en "Motivo de la cancelación" (índices desfasados). | `listaTareasEliminadas.php` (157-176). |

## C. Código sin referencias claras

| Archivo / símbolo | Situación |
|---|---|
| `vistaAgente/listaAgentes.php` | No incluido por ningún controlador; referencia `$listEncAgentes` y `c_bajaAgente.php` inexistentes. |
| `vistaAgente/ajax/filtroEstado.php` | Sin referencias en el repositorio. |
| `vistaAgente/ajax/filtroRadios.php` | Sin referencias; enlaza a `accion=editarTarea` no implementada. |
| `vistaAgente/ajax/buscarPorFecha.php` | Sin referencias. |
| `m_consultas.php::listarDireccionesCodigo` | Sin llamadas. |
| `m_consultas.php::asignarRolUsuario` | Sin llamadas. |
| `m_consultas.php::listarRoles` | Usado sólo en `headerNav.php` y `c_admin.php::listarUsuariosBajaContr`. |
| `c_eliminarDireccion.php` | Sin enlace desde la UI. |
| `styles/headerNav.css` | Usado por `headerNav.php` (sí referenciado). |
| `Chart.js` en `inicio.php` | Cargado sin uso. |

## D. Decisiones que requieren confirmación humana antes de la Fase 1

1. Resolver Q-001, Q-002, Q-003, Q-005, Q-014, Q-015 y Q-021 (bloqueantes): definen autenticación, autorización, visibilidad, ownership de tareas, concepto de agente y estrategia de datos.
2. Confirmar si los defectos de §Q-007, Q-010, Q-011, Q-006 y Q-009 se corrigen o se replican.
3. Definir la política de historial/auditoría (Q-017) y de comprobantes (Q-019).
4. Confirmar el alcance de los datos históricos a migrar.
5. Confirmar si las funcionalidades huérfanas se descartan.

> Hasta que estas preguntas se respondan, **no debe comenzar la implementación** de la nueva aplicación.
