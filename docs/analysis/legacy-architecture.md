# Arquitectura del sistema legado

> Fase 0 — análisis técnico. Este documento separa deliberadamente **problemas de implementación** de **reglas de negocio**.
> Ninguna conclusión de este documento debe interpretarse como un requisito funcional: las reglas están en [`business-rules.md`](./business-rules.md).

## 1. Arquitectura actual

### 1.1 Estilo
Aplicación **monolítica de servidor**, PHP puro (sin framework, sin Composer, sin autoload), con renderizado HTML en el servidor y JavaScript de mejora progresiva (jQuery + Bootstrap + DataTables + SweetAlert2).

No hay:
- Capa de API (todo son formularios HTML y redirecciones).
- Inyección de dependencias.
- Enrutador centralizado más allá de un `switch` en una vista.
- Plantillas (las vistas son PHP con HTML embebido).
- Pruebas automatizadas, linters, CI ni entorno de desarrollo reproducible.
- Separación real MVC: la "vista" incluye lógica de negocio y consultas; el "controlador" hace de endpoint y de redirector.

### 1.2 Capas de facto

```
Navegador
   │  formularios POST / enlaces GET
   ▼
vistaAgente/*.php            ← pantallas + enrutador (index.php) + lógica de presentación
   │  require de controladores
   ▼
controlador/c_*.php          ← leen $_POST/$_GET, instancian el modelo, redirigen
   │  require del modelo
   ▼
modelo/m_consultas.php       ← SQL + conexión mysqli (hereda de Conexion)
modelo/m_estadisticas.php    ← SQL de conteos (hereda de Conexion)
modelo/m_conexion.php        ← conexión mysqli (credenciales hardcodeadas)
modelo/m_conexionPage.php    ← conexión mysqli para endpoints AJAX
   │
   ▼
MySQL / MariaDB  (base `mantenimiento`)
```

### 1.3 Puntos de entrada

| Entrada | Tipo | Observación |
|---|---|---|
| `vistaAgente/login.php` | Vista pública | Único acceso previsto sin sesión. |
| `vistaAgente/index.php` | Enrutador | `switch` sobre `$_GET['accion']`; 8 acciones. |
| `vistaAgente/inicio.php` | Vista directa | Panel; accesible por URL. |
| `controlador/*.php` | Endpoints directos | 18 archivos invocables por URL; **sin validación de sesión ni rol**. |
| `vistaAgente/ajax/*.php` | Endpoints AJAX | 3 archivos; 2 de validación + 3 de filtrado (estos últimos incompatibles con el esquema). |
| `vistaAgente/validacionDatosRepetidos/*.php` | Endpoints AJAX | Validaciones de unicidad. |
| `vistaAgente/logout.php`, `destroySession.php` | Utilidades | Sin protección. |

### 1.4 Flujo de una petición típica

1. El navegador envía un formulario a `controlador/c_*.php`.
2. El controlador hace `session_start()`, `require('../modelo/m_consultas.php')`, lee `$_POST`, instancia `new Consultas()`.
3. El método del modelo abre una conexión **nueva** (`parent::conexionBD()`), ejecuta SQL concatenado y devuelve `bool`/array.
4. El controlador escribe una bandera en `$_SESSION` (por ejemplo `tareaOK`) y hace `header('Location: ...')`.
5. La vista de destino incluye `headerNav.php`, lee la bandera, muestra un SweetAlert y la elimina.

Este patrón "flash message en sesión" se repite en todas las operaciones.

## 2. Organización del código

| Directorio | Archivos | Responsabilidad real |
|---|---|---|
| `controlador/` | 18 `c_*.php` | Endpoints de escritura (15) + controlador de vistas `c_admin.php` (1) + login (1) + logout no (está en vista). Mezclan lectura de entrada, orquestación y redirección. |
| `modelo/` | 4 archivos | `m_consultas.php` (SQL de todo el dominio, 648 líneas), `m_estadisticas.php` (SQL de conteos), `m_conexion.php` (clase base), `m_conexionPage.php` (función suelta de conexión). |
| `vistaAgente/` | 16 archivos + 2 subdirectorios | Vistas HTML/PHP, enrutador (`index.php`), navegación (`headerNav.php`), librería de estilos, logout y utilidades. |
| `scripts/` | `alerta.php` | Helper que emite un `Swal.fire` desde PHP. |
| `styles/` | `styles.css`, `headerNav.css` | Estilos propios (el resto es Bootstrap por CDN). |
| Raíz | `mantenimiento.sql`, `README.md` | Dump del esquema y descripción mínima. |

### 2.1 Discrepancias entre nombres y responsabilidades (verificadas)

| Archivo / nombre | Qué hace realmente |
|---|---|
| `controlador/c_admin.php` | **No** es "el controlador del administrador": es el controlador de **todas las vistas** (tareas, áreas, direcciones, usuarios, estadísticas, blanqueo), sin relación con el rol. |
| `vistaAgente/index.php` | Es el enrutador de acciones, no la portada. La portada real es `inicio.php`. |
| `vistaAgente/listaAgentes.php` | No se usa; referencia un controlador inexistente. |
| `validacionDatosRepetidos/validarAreaExistente.php` | Valida **direcciones**, no áreas. |
| `m_conexionPage.php` | Conexión duplicada para endpoints AJAX, sin reutilizar la clase. |
| `c_altaAgente.php` / `c_bajaUsuario.php` | "Agente" y "usuario" son sinónimos en el dominio, pero los nombres son inconsistentes. |
| `estadisticas.php` (vista) | Contiene el render de tablas; la lógica de negocio está en `c_admin.php` y `m_estadisticas.php`. |
| `listarUsuarios` vs `listarUsuariosCargados` | Dos listados casi idénticos con distinto uso; el segundo añade `ultimoAcceso`. |

### 2.2 Acoplamientos

- **Rutas relativas dependientes del directorio de trabajo.** `c_admin.php` hace `require('libreriaEstilos.php')` y `require('headerNav.php')` — archivos que viven en `vistaAgente/`, no en `controlador/`. Funciona únicamente porque el script ejecutado es `vistaAgente/index.php` y el *include path* relativo se resuelve desde el directorio actual. Si se invoca el controlador desde otro contexto, falla.
- **`session_start()` repetido.** `index.php` inicia sesión y luego `headerNav.php` vuelve a llamarlo, después de que `c_admin.php` ya emitió un `<title>` con `echo`. Esto genera advertencias de "headers already sent" que quedan ocultas por `error_reporting(0)`.
- **Modelo global.** `m_consultas.php` concentra autenticación, usuarios, tareas, áreas, direcciones y estadísticas básicas. Cualquier cambio de esquema impacta en todo el sistema.
- **Conexión por consulta.** Cada método abre una conexión nueva; no hay pooling ni transacciones.
- **Estado de UI en sesión.** Las banderas de éxito/error se acumulan en `$_SESSION` y las consumen las vistas; no hay contrato ni limpieza centralizada.
- **Dependencia de nombres de estado como literales.** `m_estadisticas.php` y las vistas comparan `'Pendiente'`, `'En Progreso'`, `'Completo'`, `'Cancelado'`, `'Eliminado'` en lugar de usar los ids. Renombrar un estado en la base rompe la aplicación sin error de compilación.

## 3. Acceso y manipulación de datos

### 3.1 Consultas
- **Todas** las consultas se construyen por concatenación de cadenas con valores provenientes de `$_POST`, `$_GET` o `$_SESSION`:
  - `"SELECT * FROM usuario WHERE usuario = '$usuario' OR correo = '$usuario'"` (`m_consultas.php:11`)
  - `"INSERT ... VALUES ('$descripcion', ...)"` (`m_consultas.php:400-401`)
  - `"UPDATE ... WHERE nroArreglo = '$nroArreglo'"` (todas las operaciones de tarea)
- No se usa `mysqli_prepare`/`bind_param` ni un ORM.
- No se escapan valores (`mysqli_real_escape_string` no aparece en el código).
- La única mitigación es JavaScript: `validarComillas` reemplaza `'` por `"` en algunos campos de tarea (`listarTareasAdmin.php:95-104`). Es evitable y no cubre el resto de los formularios.

### 3.2 Persistencia
- Motor InnoDB, base `mantenimiento`, credenciales **hardcodeadas** en dos archivos (`m_conexion.php:10`, `m_conexionPage.php:4`): usuario `root`, sin contraseña, puerto 3306.
- `set_charset("utf8")` mientras la base se crea en `utf8mb4` (mezcla de codificaciones; riesgo con emojis y ciertos caracteres).
- Sin migraciones ni versionado del esquema: el único artefacto es un dump manual de HeidiSQL.
- Sin transacciones en operaciones compuestas (no hay operaciones compuestas, pero tampoco protección si se agregan).
- SQL embebido dentro de la capa de modelo, con nombres de columna repetidos en decenas de consultas.

### 3.3 Integridad
- FKs sólo en `tareas` → `areas`/`direcciones`/`estadotarea` y `usuario` → `tipousuario`.
- **Sin FK** entre `tareas.usuarioCreado` (texto) y `usuario.legajo`.
- **Sin índices únicos** en `usuario.usuario` ni `usuario.correo`.
- **Sin `ON DELETE`/`ON UPDATE`** explícitos: el comportamiento por defecto (RESTRICT) produce errores silenciosos porque `mysqli_query` devuelve `false` y el código no lo distingue de un error de conexión.
- `direcciones.codigo` sin `AUTO_INCREMENT` y editable, lo que permite romper referencias.

### 3.4 Archivos
- El comprobante PDF se guarda en `comprobantes/` (fuera del árbol versionado) con el nombre original del archivo.
- Sin validación de MIME, extensión ni tamaño en el servidor.
- Sin nombres únicos: dos archivos con el mismo nombre se sobrescriben.
- Sin registro en base más allá de la ruta; si el archivo se borra, el enlace queda roto.
- Sin control de acceso a la descarga.

## 4. Manejo de errores y excepciones

Patrones observados, todos problemáticos:

| Patrón | Ejemplo | Consecuencia |
|---|---|---|
| `try { ... } catch (Exception $e) { $e->getMessage(); }` (resultado descartado) | `autenticarUsuario`, `listarAreas`, `editarTarea`, `iniciarTarea` | El error se ignora; el método devuelve `null` y la vista falla más adelante o muestra éxito falso. |
| `die('Error' . $e->getMessage())` | `listarUsuarios`, `agregarUsuario`, `agregarDireccion` | Expone detalles internos y corta la aplicación. |
| `error_reporting(0)` / `error_reporting(E_ALL ^ E_NOTICE)` | todas las vistas y `index.php` | Oculta warnings y errores reales, incluidos los de SQL y variables no definidas. |
| No verificar el retorno de `mysqli_query` | `iniciarTarea`, `cancelarTarea`, `eliminarTarea`, `terminarTarea`, `bajaUsuario`, `editarDatosUsuario` | `mysqli_query` devuelve `false` ante error de SQL; el método devuelve `false`, el controlador no marca la bandera de éxito, pero tampoco informa el error. |
| `header("location:javascript:history.go(-1)")` como manejo de acción inválida | `index.php`, `c_admin.php` | Redirección inválida (no es una URL); el navegador puede ignorarla. |
| `header('location: ' . $_SERVER['HTTP_REFERER'])` | `c_iniciarTarea`, `c_terminarTarea`, `c_cancelarTarea`, `c_editarTarea` | Si no hay `Referer`, redirige a una cadena vacía; además es un open redirect potencial. |
| Sin logging | todo el sistema | Imposible diagnosticar en producción. |

## 5. Gestión de sesiones y permisos (técnico)

| Aspecto | Estado observado |
|---|---|
| Inicio de sesión | `session_start()` en cada entrada; sin `session_regenerate_id()` → **fijación de sesión**. |
| Expiración | Control por página con `$_SESSION['time']` (60 min); no hay `session.gc_maxlifetime` configurado ni control en controladores. |
| Cookies | Sin flags `HttpOnly`/`Secure`/`SameSite` explícitos (dependen de `php.ini`). |
| CSRF | **Ausente** en todos los formularios. |
| Autorización | Sólo en la interfaz (ver BR-013); el backend no valida rol ni sesión. |
| Escalada de privilegios | Un rol 4 puede invocar `c_agregarUsuario.php`, `c_cambiarPass.php`, etc. Un visitante sin sesión también. |
| IDOR | `c_editarUsuario.php` opera sobre `userAnterior` enviado por el cliente; la autogestión podría apuntar a otro usuario si se manipula el formulario (no hay verificación de que `userAnterior == $_SESSION['username']`). |
| Contraseñas | Hash correcto (bcrypt), pero sin política de complejidad, sin bloqueo y con blanqueo administrativo sin auditoría. |
| Secretos | Credenciales de base hardcodeadas y versionadas. |

## 6. Deuda técnica (clasificada)

### 6.1 Seguridad (crítica)
1. Inyección SQL en el 100 % de las consultas.
2. Ausencia de autorización en el backend (18 controladores).
3. Ausencia de CSRF.
4. Ausencia de validación de archivos subidos.
5. XSS almacenado: todas las salidas usan `<?= $valor ?>` sin `htmlspecialchars` (descripciones, nombres, motivos, notas). Un usuario puede inyectar HTML/JS que se ejecuta en la sesión de otro.
6. Credenciales de base hardcodeadas.
7. Acciones de cambio de estado por `GET`.
8. Sin regeneración de sesión.
9. Comprobantes accesibles sin autenticación.

### 6.2 Corrección funcional (alta)
1. La nota electrónica no se guarda al crear y se borra al editar (clave `ne` vs `nota_electronica`).
2. La autogestión falla silenciosamente si el login fue por correo (`WHERE usuario = <correo>`).
3. El blanqueo de contraseña lee `pass` en lugar de `nuevaPass`.
4. `contarEstadoHoy` compara `DATETIME` con `CURDATE()`.
5. `estadisticas.php` indexa `[0][0]` sobre escalares.
6. Los listados de tareas desaparecen si el creador se da de baja o cambia de nombre.
7. La validación de código de dirección en edición bloquea el propio registro.
8. `validarAreaExistente.php` se usa para direcciones (nombre engañoso).
9. Cambiar el código de una dirección con tareas asociadas falla por FK sin mensaje.
10. La fecha de la barra lateral depende de una variable no inicializada.
11. En el detalle de tareas eliminadas, el modal rotula "Materiales utilizados" pero imprime el nombre del estado (`$tarea[6]`), y rotula "Motivo de la cancelación" pero imprime la fecha del problema (`$tarea[8]`), por un desfase de índices (`listaTareasEliminadas.php:158-176`).

### 6.3 Diseño (media)
1. `m_consultas.php` monolítico (648 líneas, 6 dominios).
2. Modelo que mezcla persistencia, reglas y formato.
3. Vistas con lógica de negocio y consultas embebidas.
4. Enrutador por `switch` en una vista.
5. Dependencia del directorio de trabajo para los `require`.
6. Conexión nueva por consulta.
7. Duplicación de la lógica de conexión (`m_conexion.php` vs `m_conexionPage.php`).
8. Duplicación de consultas de tareas (5 variantes casi idénticas en `listarTareasAdmin`, `listarTareasCompletasActual`, `listarTareasEliminadas`).
9. Estados referenciados por nombre en lugar de por id.
10. Sin DTOs ni validación centralizada.

### 6.4 Operación (media)
1. Sin entorno configurable (dev/prod).
2. Sin migraciones.
3. Sin logs.
4. Sin pruebas.
5. Dependencias por CDN (la aplicación no funciona sin Internet).
6. Sin documentación técnica más allá de este análisis.

### 6.5 Código muerto / inconsistente (baja, pero relevante para la migración)
- `listaAgentes.php`, `ajax/filtroEstado.php`, `ajax/filtroRadios.php`, `ajax/buscarPorFecha.php`.
- `listarDireccionesCodigo`.
- `c_eliminarDireccion.php` (sin enlace).
- Chart.js cargado sin uso.
- Restos de un modelo anterior (`usuario_legajo`, `area_codigo`, `nombreAfectado`, concepto de agente asignado).

## 7. Riesgos de migración

| Riesgo | Impacto | Mitigación sugerida (fase posterior) |
|---|---|---|
| **Reglas implícitas en la UI, no en el backend** | Alto: si se replica sólo el backend, se pierden restricciones (p. ej. editar sólo Pendiente/En Progreso) o se heredan agujeros. | Convertir cada BR en validación de dominio explícita y testeable. |
| **Defectos que parecen reglas** (nota electrónica, estadísticas, tareas que desaparecen) | Alto: migrar el bug o "corregirlo" sin aprobación. | Resolver las preguntas de `open-questions.md` antes de implementar. |
| **`usuarioCreado` como texto** | Alto: impide integridad y auditoría; al migrar hay que decidir cómo resolver homónimos y datos históricos. | Diseñar FK a usuario y estrategia de backfill. |
| **Estados como literales y borrado lógico mezclado con el ciclo de vida** | Medio: acopla informes y filtros. | Separar estado funcional de indicador de baja lógica. |
| **Datos históricos incompletos** (sin autor de inicio/cierre, sin historial) | Medio: la nueva app no podrá reconstruir la trazabilidad pasada. | Definir qué se muestra de datos históricos y qué se registra desde cero. |
| **Comprobantes en disco sin metadatos** | Medio: migración de archivos y validación de integridad. | Inventariar archivos, definir almacenamiento y checksum. |
| **Comportamiento de sesión y expiración** | Bajo/medio: la nueva app usará JWT/cookies; el "60 min exactos" debe confirmarse. | Decidir política de sesión con el negocio. |
| **Dependencia de la zona horaria del servidor** | Medio: `NOW()`/`CURDATE()` usan la zona de MySQL. | Fijar UTC y política de visualización. |
| **Datos con codificación mixta** (`utf8` vs `utf8mb4`) | Bajo/medio: posibles caracteres corruptos. | Validar y normalizar en la migración. |
| **Reglas no documentadas sobre roles** | Medio: si existen roles 1/2 históricos, el modelo nuevo debe contemplarlos o justificar su ausencia. | Confirmar catálogo de roles (Q-003). |
| **Expectativa de "agente asignado"** | Medio: la UI y los archivos huérfanos lo insinúan, pero el modelo no lo tiene. | Confirmar si es una funcionalidad nueva o una omisión (Q-015). |

## 8. Dependencias externas

### 8.1 Runtime
- PHP 7/8 con extensión `mysqli` (el código usa `mysqli_*` y `password_*`).
- MySQL o MariaDB (el dump proviene de MariaDB 10.4.27).
- Servidor web Apache/Nginx con PHP (no hay `.htaccess` ni configuración incluida).

### 8.2 Cliente (CDN)
| Dependencia | Versión | Uso |
|---|---|---|
| Bootstrap | 5.1.3 (login) y 5.2.1 (resto) | Layout y componentes. **Dos versiones distintas** en la misma app. |
| Bootstrap Icons | 1.8.1 | Iconografía. |
| jQuery | 3.6.0 | Base de DataTables y scripts propios. |
| DataTables | 1.11.5 (+ Bootstrap 5, Buttons 2.2.2) | Tablas, paginación, exportación. |
| JSZip | 3.1.3 | Requerido por exportación Excel. |
| pdfmake | 0.1.53 (+ vfs_fonts) | Exportación PDF. |
| SweetAlert2 | 11 | Alertas. |
| Animate.css | 4.1.1 | Animaciones (cargado, uso marginal). |
| Chart.js | 3.8.0 | Cargado en `inicio.php`, **sin uso**. |

### 8.3 Servicios
- Ninguno. No hay correo, almacenamiento externo, autenticación federada ni colas.

## 9. Aspectos que **no** deben trasladarse necesariamente

Se listan como decisiones de diseño, no como reglas:

1. **La arquitectura de carpetas y el enrutador por `switch`.** La nueva app debe separar API, dominio, infraestructura y frontend.
2. **El modelo de permisos por comparación de enteros** (`== 3`). Debe reemplazarse por autorización declarativa (políticas/roles/claims).
3. **La sesión PHP y el control de expiración por página.** Reemplazar por un mecanismo centralizado.
4. **Las credenciales hardcodeadas y la conexión por consulta.**
5. **El patrón de "flash message en sesión"** para feedback; en React será estado de UI y códigos HTTP.
6. **La duplicación de consultas y la lógica en las vistas.**
7. **El uso de nombres de estado como literales.**
8. **El borrado lógico conviviendo con el catálogo de estados** (decisión de modelado a revisar).
9. **El esquema `direcciones.codigo` manual sin autoincrement.**
10. **El almacenamiento de comprobantes en disco sin metadatos.**
11. **Las validaciones sólo de cliente** (unicidad de usuario, rango de legajo, PDF, coincidencia de contraseñas).
12. **Los defectos listados en §6.2**, que no son comportamiento deseado.
13. **Los archivos huérfanos y el código muerto.**

## 10. Observaciones que condicionan la arquitectura destino

- El dominio es pequeño (5 entidades fuertes + 2 catálogos), lo que hace viable un diseño limpio con EF Core y una API mínima.
- No hay requisitos de tiempo real, integraciones ni multi-tenancy evidenciados.
- La ausencia de historial y de asignación son **decisiones de producto pendientes**, no restricciones técnicas.
- El volumen esperado es bajo/medio (listados sin paginación server-side, exportación cliente completa); aun así, el nuevo diseño debería prever paginación y filtros.
- La migración de datos históricos requiere resolver primero: homónimos en `usuarioCreado`, usuarios de baja referenciados por tareas, archivos de comprobantes y codificación de textos.

## 11. Conclusión técnica

El sistema es funcionalmente pequeño pero técnicamente frágil: **no existe una capa de dominio**, las reglas viven repartidas entre SQL, controladores y JavaScript, y la seguridad depende de la interfaz. La migración debe tomar `business-rules.md` como fuente de comportamiento y **no** el código como fuente de diseño. Los defectos identificados deben decidirse uno por uno con el negocio antes de escribir la nueva implementación (ver `open-questions.md`).
