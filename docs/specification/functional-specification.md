# Especificación funcional consolidada

> **Fase 1 — Revisión 4.** Incorpora `docs/specification-definitions.md` como fuente de mayor prioridad.
> Estado: propuesta para revisión. No se inició la Fase 2 ni se escribió código.
>
> **Regla transversal:** ninguna regla de negocio del legado es válida sin aprobación explícita. Las reglas heredadas se clasifican en aprobadas, pendientes, reemplazadas o descartadas. No se asumen por provenir del PHP original.

## Índice

1. [Control documental](#1-control-documental)
2. [Propósito, fuentes y prioridad](#2-propósito-fuentes-y-prioridad)
3. [Cambios principales de la revisión 4](#3-cambios-principales-de-la-revisión-4)
4. [Contexto funcional y terminología](#4-contexto-funcional-y-terminología)
5. [Actores y roles aprobados](#5-actores-y-roles-aprobados)
6. [Clasificación de funcionalidades](#6-clasificación-de-funcionalidades)
7. [Requisitos funcionales por módulo](#7-requisitos-funcionales-por-módulo)
8. [Reglas aprobadas RN-001 a RN-118](#8-reglas-aprobadas-rn-001-a-rn-118)
9. [Reglas aprobadas en la segunda ronda PR-001 a PR-017](#9-reglas-aprobadas-en-la-segunda-ronda-pr-001-a-pr-017)
10. [Trazabilidad de las definiciones Q-001 a Q-026](#10-trazabilidad-de-las-definiciones-q-001-a-q-026)
11. [Disposición de las reglas heredadas BR-001 a BR-054](#11-disposición-de-las-reglas-heredadas-br-001-a-br-054)
12. [Contradicciones y ambigüedades](#12-contradicciones-y-ambigüedades)
13. [Fuera de alcance](#13-fuera-de-alcance)
14. [Criterios de aceptación funcionales](#14-criterios-de-aceptación-funcionales)
15. [Riesgos funcionales](#15-riesgos-funcionales)
16. [Decisiones](#16-decisiones)

---

## 1. Control documental

| Campo | Valor |
|---|---|
| Fase | 1 — Especificación funcional y diseño del dominio |
| Revisión | 4 |
| Estado | Propuesta para revisión y aprobación |
| Fuente de mayor prioridad | `docs/specification-definitions.md` (no modificado) |
| Fuente funcional original | `docs/definitions.md` (no modificado) |
| Evidencia del legado | `docs/analysis/*` (sólo informa; no aprueba reglas) |
| Alcance | Documentación; sin código, sin proyectos, sin dependencias, sin migraciones |
| Sistema legado | Conservado íntegramente en `/legacy`; no modificado |

## 2. Propósito, fuentes y prioridad

Esta especificación consolida el comportamiento del sistema de gestión de tickets para que sirva de base a:

- una **API ASP.NET Core** con dominio propio;
- un **frontend React** independiente;
- una implementación futura que no replique los defectos del PHP legado.

### 2.1 Prioridad de fuentes

| Prioridad | Fuente | Uso |
|---|---|---|
| 1 | `docs/specification-definitions.md` | Decisiones aprobadas de Fase 1; prevalece ante diferencias. |
| 2 | `docs/definitions.md` | Definiciones funcionales y de negocio originales. |
| 3 | `docs/analysis/*` | Evidencia del legado. No aprueba reglas por sí sola. |

> **Nota de árbol de trabajo:** `docs/definitions.md` se cita por su nombre funcional; en el árbol actual su contenido está archivado como `docs/analysis-definitions.md`. El cambio de nombre es preexistente y no fue realizado por esta revisión.

Si una contradicción no puede resolverse con las dos primeras fuentes, se documenta como decisión pendiente y se solicita intervención del usuario. No se completa por suposición.

### 2.2 Alcance de esta fase

Incluye:

- funcionalidades aprobadas, corregidas, nuevas, descartadas y pendientes de validación funcional;
- reglas de negocio aprobadas, reemplazadas y descartadas;
- criterios de roles, permisos, ciclo de vida, asignación, historial, auditoría, comprobantes y estadísticas;
- el impacto de las decisiones D-01 a D-39.

No incluye:

- decisiones de implementación (frameworks, ORM, estructura de soluciones);
- esquemas de base de datos ni migraciones;
- contratos definitivos de DTOs;
- código C# o JavaScript.

## 3. Cambios principales de la revisión 4

1. **Entidad `Ticket` y tipos.** El requerimiento se llama `Ticket`. Existen dos tipos: `INCIDENT` (problema específico reportado por un área) y `TASK` (problema preexistente o mejora a realizar en un área).
2. **Modelo en inglés.** Entidades, atributos, estados, roles, permisos y eventos usan nombres en inglés. Ejemplo: `fechaProblema` pasa a `problem_date`; `Tarea` pasa a `Ticket`.
3. **Ninguna regla del legado es válida sin aprobación explícita.** Se elimina la categoría “heredada y por eso definitiva”. Las BR se clasifican en §11.
4. **Cuatro roles exactos:** `SUPERUSER`, `ADMINISTRATIVE`, `AREA_MANAGER`, `AGENT`. No existe el rol “Consulta”.
5. **Multi-área:** un usuario puede pertenecer a una o más áreas mediante `UserAreaMembership`; tiene un solo rol por área.
6. **Visibilidad del `AGENT`:** tickets asignados y tickets sin asignar de su área.
7. **Asignación:** el `AGENT` puede tomar tickets sin asignar; iniciar no exige responsable.
8. **Reapertura:** los tickets cerrados son inmutables, pero pueden reabrirse si reciben quejas. D-30 define destino, motivo, responsables y conservación de datos de cierre.
9. **Correo obligatorio e interno.** El correo es obligatorio y debe pertenecer a la organización.
10. **Zona horaria configurable.** `America/Argentina/Buenos_Aires` por defecto; el Administrador puede cambiarla.
11. **Auditoría de negocio.** Se auditan sólo cambios de negocio; se retiene todo en principio. No se auditan descargas ni lecturas.
12. **Catálogo fijo de roles.** No hay gestión dinámica de roles; `P-ROLE_MANAGE` (denominación en inglés de `P-ROL-GESTIONAR`, D-17) queda reservado.
13. **Exportación y archivos.** CSV/XLSX para listados, PDF para reportes; comprobante PDF obligatorio, sin reemplazo tras completar.
14. **Concurrencia.** ETag/`If-Match`, conflicto `409` e `Idempotency-Key` opcional para altas.
15. **Dirección derivada.** La dirección del ticket se obtiene de `Area.direction_id`; se elimina `affected_direction_id` (D-29).
16. **Reapertura y cierres.** La reapertura vuelve a `PENDING` o `IN_PROGRESS`, exige motivo y el mismo responsable del cierre; los datos de cierre se guardan en `TicketClosure` y un ticket puede tener varios cierres históricos (D-30).
17. **Permisos operativos.** `ADMINISTRATIVE` inicia y reabre en su área, pero no completa ni restaura; `AREA_MANAGER` completa, restaura y reabre en su área; `AGENT` opera sus propios tickets (D-31).
18. **Gestión de usuarios y configuración.** Sólo el `SUPERUSER` gestiona usuarios; el usuario edita nombre, apellido, correo y contraseña; `/settings` sólo expone `timezone`; se protege el último `SUPERUSER` activo (D-33, D-36, D-37).
19. **Listados, login y auditoría.** El listado actual es `PENDING` + `IN_PROGRESS`; el login acepta usuario o correo y registra `last_login_at`; la auditoría de negocio se separa de los logs de seguridad (D-35, D-38, D-39).

## 4. Contexto funcional y terminología

### 4.1 Qué es el sistema

Sistema de gestión de **tickets** de un área operativa que resuelve problemas a toda la organización. El caso observado es mantenimiento, pero el diseño permite incorporar otras áreas operativas con sus tickets separados según la pertenencia de cada usuario.

El sistema permite:

1. registrar tickets;
2. clasificarlos como `INCIDENT` o `TASK`;
3. asociarlos a un área responsable; la dirección se deriva del área (D-29);
4. asignar un responsable;
5. seguir su ciclo de vida;
6. registrar la solución y un comprobante;
7. conservar historial y auditoría;
8. consultar estadísticas;
9. administrar usuarios, roles, áreas, direcciones y configuración básica.

### 4.2 Dirección, área y dirección del ticket

La decisión D-04 establece:

- una `Direction` agrupa un conjunto de áreas;
- cada `Area` debe tener `direction_id`;
- “área operativa” es la misma entidad que “área dependiente de una dirección”.

La decisión D-29 precisa que la dirección del ticket se deriva siempre de `Area.direction_id`; no existe `affected_direction_id` ni una dirección afectada independiente del área. El ticket sólo referencia `area_id` y la dirección se obtiene por relación.

### 4.3 Glosario

| Término | Definición |
|---|---|
| **Ticket** | Unidad central del negocio. Reemplaza a “tarea” cuando se habla del requerimiento. |
| **`INCIDENT`** | Ticket que representa un problema específico reportado por un área. No admite `problem_date` futura. |
| **`TASK`** | Ticket que representa un problema preexistente o una mejora a realizar en un área. Admite `problem_date` futura. |
| **`PENDING`** | Estado inicial aprobado (D-34); el ticket aún no comenzó. |
| **`IN_PROGRESS`** | Estado de ejecución. |
| **`COMPLETED`** | Estado cerrado con solución y comprobante. |
| **`CANCELLED`** | Estado cerrado sin ejecución. |
| **`UserAreaMembership`** | Entidad intermedia usuario–área–rol. Un usuario puede tener varias; una por área, con un solo rol por área. |
| **`Direction`** | Unidad organizacional que agrupa áreas. Tiene código, nombre, descripción y `is_active`. |
| **`Area`** | Unidad organizacional dependiente de una dirección; ámbito operativo de usuarios y tickets. Tiene `direction_id` e `is_active`. |
| **Creador** | Usuario que registra el ticket. No es necesariamente el responsable ni quien ejecuta las transiciones. |
| **Responsable** | Usuario actualmente accountable de ejecutar el ticket. Puede estar vacío. |
| **Ejecutor de una transición** | Usuario que realiza una acción concreta (iniciar, completar, cancelar, reabrir, eliminar, restaurar). Se registra en historial y auditoría; puede diferir del responsable. |
| **Eliminación lógica** | Marca de eliminación sin borrar datos; es restaurable y no es un estado de ciclo de vida. |
| **Restauración** | Acción que quita la marca de eliminación y devuelve el ticket a su estado de ciclo de vida previo. |
| **Reapertura** | Acción que saca un ticket de `COMPLETED` o `CANCELLED` y lo devuelve a `PENDING` o `IN_PROGRESS`, con motivo obligatorio y el mismo responsable del cierre (D-30). |
| **Comprobante** | Archivo PDF asociado a un ticket completado. |
| **Nota Electrónica** | Documento externo gestionado por otro sistema administrativo. En este sistema se persiste su identificador. |
| **Historial de estados** | Secuencia inmutable de transiciones de estado de un ticket. |
| **Auditoría de negocio** | Registro de cambios de negocio con actor, fecha y datos anteriores/nuevos. No incluye lecturas ni descargas. |
| **Alcance (scope)** | Conjunto de datos sobre el que un usuario puede operar o consultar. Es distinto del permiso de acción. |

## 5. Actores y roles aprobados

| Actor / rol | Código | Descripción | Alcance aprobado |
|---|---|---|---|
| Administrador | `SUPERUSER` | Administra el sistema completo. | Global. |
| Administrativo | `ADMINISTRATIVE` | Crea tickets, edita los creados por él y asigna. | Sus áreas, con edición limitada a tickets creados por él. |
| Responsable de área | `AREA_MANAGER` | Crea, edita, asigna, reasigna, cancela y elimina tickets de su área. | Sus áreas. |
| Agente | `AGENT` | Toma tickets sin asignar y ejecuta los propios. | Tickets asignados y sin asignar de sus áreas. |

No existe el rol “Consulta” en la primera versión. El catálogo es fijo (D-02, D-17).

### 5.1 Multi-área y rol por área

Un usuario puede tener una o más `UserAreaMembership`. Cada membresía vincula:

- un `user_id`;
- un `area_id`;
- un `role_id`;
- un `is_active`.

Un usuario tiene **un solo rol por área**. Puede tener roles distintos en áreas distintas. Si tiene una membresía `SUPERUSER`, su alcance es global. Si tiene `ADMINISTRATIVE` en un área y `AGENT` en otra, actúa como Administrativo en la primera y como Agente en la segunda.

## 6. Clasificación de funcionalidades

### 6.1 Funcionalidades aprobadas explícitamente

| ID | Funcionalidad | Origen | Nota |
|---|---|---|---|
| F-A-01 | Autenticación con cookie `HttpOnly` y sesión de servidor | D-07 | No se usa token en `localStorage`. |
| F-A-02 | Expiración deslizante de 60 minutos | D-07 | Centralizada en backend. |
| F-A-03 | Invalidación de sesiones al dar de baja, cambiar rol/área o blanquear contraseña | D-07 | También al reactivar se exige nuevo blanqueo. |
| F-A-04 | Cuatro roles fijos | D-02 | `SUPERUSER`, `ADMINISTRATIVE`, `AREA_MANAGER`, `AGENT`. |
| F-A-05 | Multi-área con un rol por área | D-21 | `UserAreaMembership`. |
| F-A-06 | Tickets `INCIDENT` y `TASK` | `specification-definitions.md` | Reemplaza la entidad “Tarea”. |
| F-A-07 | Número de ticket secuencial global e inmutable | D-26 | Simple, sin prefijo por área. |
| F-A-08 | `problem_date` editable en tickets abiertos | D-10 | Fecha y hora; futura sólo para `TASK`. |
| F-A-09 | Nota Electrónica como identificador externo opcional | D-09 | Sin checkbox separado. |
| F-A-10 | Asignación, reasignación y toma de tickets | D-05, D-12 | Responsable opcional; iniciar no lo exige. |
| F-A-11 | Reapertura de tickets cerrados | D-13/D-30 | Aprobada; destino `PENDING`/`IN_PROGRESS`, motivo y mismo responsable. |
| F-A-12 | Eliminación lógica y restauración | D-01 | La eliminación no es un estado. |
| F-A-13 | Comprobante PDF obligatorio para completar | D-11 | Validación en servidor. |
| F-A-14 | Historial de estados y auditoría de negocio | Q-017, D-22 | Sólo cambios de negocio. |
| F-A-15 | Estadísticas con criterios explícitos | Q-020, D-08 | Ver §7.9. |
| F-A-16 | Exportación CSV/XLSX y PDF | D-24 | Listados y reportes. |
| F-A-17 | Zona horaria configurable | D-14 | Default `America/Argentina/Buenos_Aires`. |
| F-A-18 | Correo obligatorio e interno | D-16 | Unicidad y formato. |
| F-A-19 | Catálogo fijo de roles y permisos declarativos | D-17 | Sin CRUD de roles. |
| F-A-20 | Concurrencia optimista con ETag/`If-Match` | D-27 | `409` en conflicto. |
| F-A-21 | Seed de roles/permisos y Administrador inicial | D-28 | Para el desarrollador. |

### 6.2 Funcionalidades del legado corregidas o reemplazadas por decisión aprobada

| ID | Funcionalidad | Defecto heredado | Corrección aprobada |
|---|---|---|---|
| F-C-01 | Autorización | Sólo visual; controladores sin validación | Autorización obligatoria en backend; denegar por defecto (Q-002). |
| F-C-02 | Baja de usuario | `idRol2 = NULL`; el usuario puede seguir logueándose | Estado `INACTIVE` separado del rol; login bloqueado; sesiones invalidadas (Q-001, D-07). |
| F-C-03 | Vínculo creador–ticket | Texto `"Nombre Apellido"`; los tickets desaparecen al renombrar/dar de baja | Vínculo por `user_id`; visualización/búsqueda por legajo (Q-014, D-20). |
| F-C-04 | Nota Electrónica | No se guarda al crear y se borra al editar | Se persiste el identificador externo (Q-007, D-09). |
| F-C-05 | Transiciones de estado | `UPDATE` sin validar estado actual | Máquina de estados en dominio (Q-008, D-01, D-13). |
| F-C-06 | Edición de ticket | El endpoint permite editar completados/cancelados | Los cerrados son inmutables; se reabren o se eliminan/restauran (D-13). |
| F-C-07 | Terminar ticket | Sólo el HTML exige PDF; el backend acepta cualquier archivo | Validación de PDF y tamaño en servidor (D-11). |
| F-C-08 | Eliminación | Sin restauración ni estado previo | Eliminación lógica restaurable; se conserva el estado (D-01). |
| F-C-09 | Trazabilidad | Sin historial ni autor de transiciones | Historial de estados y auditoría de negocio (Q-017, D-22). |
| F-C-10 | Estadísticas | Criterios de fecha mezclados; render `[0][0]` sobre escalares | Criterios de Q-020/D-08 y conteos escalares (Q-011). |
| F-C-11 | Direcciones | PK manual editable, borrado físico, sin baja lógica | PK subrogada, código único editable, `is_active` (Q-018, D-19). |
| F-C-12 | Áreas | Ambos roles crean/editan | Sólo Administrador (Q-004). |
| F-C-13 | Usuarios de baja | Excluidos por inner join; sus tickets desaparecen | Tickets visibles y usuario conservado en historial/auditoría (Q-014); los inactivos se excluyen de listados operativos (D-33). |
| F-C-14 | Blanqueo de contraseña | Lee el campo de repetición | Usa la nueva contraseña y valida coincidencia en servidor (Q-009). |
| F-C-15 | Autogestión | Falla si el login fue por correo | Localiza por legajo (Q-006). |
| F-C-16 | Cambio de rol | Sólo baja + alta; método muerto | El Administrador cambia el rol de la membresía (Q-024, D-21). |
| F-C-17 | Comprobantes | Nombre original, sin metadatos, URL directa | Nombre único/relevante, metadatos y descarga autenticada (Q-019, D-25). |
| F-C-18 | Fechas | `fechaProblema` se llena con `NOW()` y no es editable | `problem_date` declarada y editable; `created_at` como timestamp (Q-023, D-10). |
| F-C-19 | Unicidad | Sólo validación AJAX de usuario; correo sin unicidad | Restricciones de unicidad en base (Q-013). |
| F-C-20 | Visibilidad | Todos ven todos los tickets | Visibilidad por rol y área (Q-005, D-03). |
| F-C-21 | Roles | Sólo SUPERUSER/Administrativo; roles nuevos informales | Cuatro roles fijos con permisos declarativos (D-02, D-17). |
| F-C-22 | Eliminación como estado | `Eliminado` mezclado con el ciclo de vida | Cuatro estados + marca de eliminación (D-01). |
| F-C-23 | Reapertura | No existía | Reapertura aprobada con destino `PENDING`/`IN_PROGRESS`, motivo y mismo responsable (D-13/D-30). |

### 6.3 Funcionalidades nuevas aprobadas

| ID | Funcionalidad | Justificación | Estado |
|---|---|---|---|
| F-N-01 | Asignación y reasignación de responsable | Q-015; la UI legada hablaba de “agentes” pero no existía el concepto | Aprobada por D-05. |
| F-N-02 | Rol `AGENT` | Q-003/Q-015; necesario para ejecutar tickets asignados | Aprobado por D-02. |
| F-N-03 | Rol `AREA_MANAGER` | Q-003; necesario para supervisar y asignar en un área | Aprobado por D-02. |
| F-N-04 | Toma de tickets sin asignar por el `AGENT` | D-12 | Aprobada. |
| F-N-05 | Historial de estados visible | Q-017 | Aprobada. |
| F-N-06 | Auditoría de cambios de negocio | Q-017/D-22 | Aprobada; sin lecturas/descargas. |
| F-N-07 | Restauración de tickets eliminados | Q-016/D-01 | Aprobada. |
| F-N-08 | Reapertura de tickets cerrados | D-13/D-30 | Aprobada; destino `PENDING`/`IN_PROGRESS`, motivo y mismo responsable. |
| F-N-09 | Búsqueda, filtros y paginación server-side | Q-025/D-35 | Aprobada; filtros por tipo, área, responsable, creador y fechas. |
| F-N-10 | Multi-área con rol por área | D-21 | Aprobada. |
| F-N-11 | Panel/estadísticas con alcance por área | Q-005/D-03 | Aprobado; criterios D-08. |
| F-N-12 | Descarga autenticada de comprobantes | Q-019 | Aprobada. |
| F-N-13 | Invalidación de sesiones activas | Q-001/D-07 | Aprobada. |
| F-N-14 | Consulta de auditoría de negocio | Q-017/D-22 | Aprobada; alcance por rol/área. |
| F-N-15 | Exportación de listados y reportes | D-24 | Aprobada; formatos definidos. |
| F-N-16 | Configuración de zona horaria por el Administrador | D-14/D-37 | Aprobada; `/settings` sólo `timezone`. |
| F-N-17 | Concurrencia optimista e idempotencia de altas | D-27 | Aprobada. |

### 6.4 Funcionalidades descartadas

| ID | Funcionalidad / práctica | Motivo |
|---|---|---|
| F-D-01 | Archivos huérfanos `listaAgentes.php`, `ajax/filtroEstado.php`, `ajax/filtroRadios.php`, `ajax/buscarPorFecha.php` | No referenciados y consultan columnas inexistentes; se reemplazan por filtros nuevos (Q-025). |
| F-D-02 | Chart.js cargado sin uso | Código muerto. |
| F-D-03 | Validaciones sólo de cliente (comillas, celular, PDF, unicidad, coincidencia de contraseñas) | No son garantías de negocio; se validan en servidor. |
| F-D-04 | Borrado físico de direcciones | Reemplazado por baja lógica (Q-018). |
| F-D-05 | `idRol2 = NULL` como baja de usuario | Reemplazado por estado `INACTIVE` separado del rol (Q-001). |
| F-D-06 | Unión por `CONCAT(nombre, ' ', apellido)` | Reemplazada por vínculo por `user_id`/legajo (Q-014, D-20). |
| F-D-07 | Comparación de estados por nombre literal | Reemplazada por enums de dominio. |
| F-D-08 | “Flash messages” en sesión | Reemplazados por respuestas de API y estado de UI. |
| F-D-09 | Acciones de cambio de estado por `GET` | Reemplazadas por operaciones HTTP explícitas. |
| F-D-10 | Credenciales hardcodeadas y SQL concatenado | Deuda técnica de seguridad; no se traslada. |
| F-D-11 | Identificadores legados `idRol2`, `idrol`, `codigoArea3` | Se reinician los índices y se usan nombres de dominio en inglés (Q-003). |
| F-D-12 | `nota_electronica varchar(45)` como texto libre | Reemplazada por identificador de documento externo (Q-007, D-09). |
| F-D-13 | `usuarioCreado` como texto | Reemplazado por vínculo a usuario (Q-014, D-20). |
| F-D-14 | Comprobante con nombre original sin metadatos | Reemplazado por almacenamiento con nombre único y metadatos (Q-019, D-25). |
| F-D-15 | `fechaCreada` como `DATE` sin hora | Reemplazada por timestamp del dato (Q-023). |
| F-D-16 | Exportación sólo cliente sobre el HTML recibido | Se redefine como funcionalidad de reporte; CSV/XLSX/PDF (D-24). |
| F-D-17 | Recuperación de contraseña por correo | No existe en el legado y no fue definida; se mantiene el blanqueo administrativo. |
| F-D-18 | Notificaciones, prioridad, SLA, categorías, comentarios, adjuntos múltiples | No evidenciados ni definidos; fuera de alcance. |
| F-D-19 | Rol “Consulta” | No aprobado por D-02. |
| F-D-20 | Gestión dinámica de roles/permisos | No aprobada por D-17; `P-ROLE_MANAGE` reservado. |
| F-D-21 | Reemplazo de comprobante tras completar | No aprobado por D-25. |
| F-D-22 | Auditoría de lecturas/descargas | No aprobada por D-22. |

### 6.5 Funcionalidades del legado: estado de aprobación

Las siguientes funcionalidades existían en el legado. Las decisiones D-29 a D-39 las aprobaron o reemplazaron según se indica:

| ID | Funcionalidad heredada | Estado | Decisión |
|---|---|---|---|
| F-P-01 | Login con usuario **o** correo | Aprobada | D-38. |
| F-P-02 | Registro de último acceso | Aprobada | D-38. |
| F-P-03 | Logout explícito | Control de seguridad, no regla de negocio | Se mantiene como requisito técnico. |
| F-P-04 | Listado “actual” = `PENDING` + `IN_PROGRESS` | Aprobada | D-35. |
| F-P-05 | Sublistado de completados hoy | Aprobada como listado de completados filtrable por fecha | D-35. |
| F-P-06 | Listado de cancelados | Aprobada | D-35. |
| F-P-07 | Listado de eliminados | Aprobada | D-35. |
| F-P-08 | Campos obligatorios del ticket | Aprobada | D-34. |
| F-P-09 | Estado inicial `PENDING` | Aprobada | D-34. |
| F-P-10 | Solución obligatoria para completar | Aprobada | D-34. |
| F-P-11 | Motivo obligatorio de cancelación | Aprobada | D-34. |
| F-P-12 | Motivo obligatorio de eliminación | Aprobada | D-34. |
| F-P-13 | Edición de campos específicos sin alterar estado/creador | Aprobada, limitada a tickets abiertos | D-34. |
| F-P-14 | Sólo el `SUPERUSER` gestiona usuarios | Aprobada | D-33. |
| F-P-15 | El usuario no puede cambiar su propio rol/área | Aprobada | D-33. |
| F-P-16 | Usuarios inactivos excluidos de listados operativos | Aprobada | D-33. |
| F-P-17 | Direcciones visibles para roles operativos | Aprobada: dirección derivada del área | D-29. |
| F-P-18 | Filtros por tipo, área, responsable, creador y fechas | Aprobada | D-35. |
| F-P-19 | Baja/cambio de área de responsable con tickets activos | Aprobada por D-06 | Se implementa con bloqueo y aviso. |
| F-P-20 | Código de dirección reutilizable si no está activo | Aprobada por D-19 | Se implementa. |
| F-P-21 | Desglose de estadísticas por área | Aprobada por D-08 | Se implementa con área actual. |
| F-P-22 | Panel “hoy” por `problem_date` | Aprobada por Q-010/D-08 | Se implementa. |

## 7. Requisitos funcionales por módulo

### 7.1 Autenticación y sesión

- Cookie de sesión `HttpOnly` y sesión de servidor (D-07).
- Expiración deslizante de 60 minutos, controlada en backend (D-07).
- Invalidación de sesiones al dar de baja, cambiar rol/área o blanquear contraseña (D-07).
- Al reactivar, exigir nuevo blanqueo de contraseña (D-07).
- Rechazo de usuarios `INACTIVE` (Q-001).
- Correo obligatorio e interno (D-16).
- Política de contraseñas configurable, bloqueo temporal y rate limiting; MFA fuera de alcance (D-23).
- Autorización obligatoria en cada operación; denegar por defecto (Q-002).
- Login por usuario o correo; se registra `last_login_at` (D-38). Preferentemente el usuario coincide con la parte local del correo.

Detalle en [`security-and-sessions.md`](./security-and-sessions.md).

### 7.2 Usuarios, roles y membresías

- Cuatro roles fijos: `SUPERUSER`, `ADMINISTRATIVE`, `AREA_MANAGER`, `AGENT` (D-02). El rol que D-02 llama Administrador se codifica `SUPERUSER`.
- Un usuario puede tener una o más `UserAreaMembership`; un solo rol por área (D-21).
- Sólo el `SUPERUSER` gestiona usuarios, membresías y roles (D-33).
- El usuario edita su nombre, apellido, correo y contraseña; no cambia su rol ni sus áreas (D-33).
- Estado `ACTIVE`/`INACTIVE` separado del rol (Q-001).
- Legajo inmutable y único (D-15, D-20).
- Nombre de usuario y correo únicos (Q-013); correo obligatorio (D-16).
- Usuarios inactivos: excluidos de listados operativos (D-33); se conservan en historial/auditoría.
- El cambio de rol/área invalida sesiones (D-07).
- Se bloquea la baja, auto-baja y cambio de rol del último `SUPERUSER` activo (D-36).

Detalle en [`roles-and-permissions.md`](./roles-and-permissions.md).

### 7.3 Áreas y direcciones

- Administración exclusiva del Administrador (Q-004).
- Una `Direction` agrupa áreas; cada `Area` tiene `direction_id` (D-04).
- Dirección con PK subrogada, código editable y único, y `is_active` (Q-018).
- Área con `is_active`; no se desactiva una dirección con áreas activas; no se desactiva un área con usuarios/tickets activos sin reasignación (D-18).
- Código de dirección editable con auditoría; se puede reutilizar un código que no esté en uso activo (D-19).
- Prohibición de borrado físico que rompa referencias históricas (D-18).
- La dirección del ticket se deriva de `Area.direction_id`; no hay dirección afectada separada (D-29).
- El ticket exige `area_id`; la dirección se obtiene por relación (D-29, D-34).

### 7.4 Tickets

- Entidad `Ticket`, con tipos `INCIDENT` y `TASK`.
- `INCIDENT`: problema específico reportado por un área; `problem_date` no puede ser futura.
- `TASK`: problema preexistente o mejora; `problem_date` puede ser futura (D-32).
- Número visible secuencial global e inmutable (D-26).
- `type`, `description`, `area_id` y `problem_date` son obligatorios; el estado inicial es `PENDING` (D-34).
- `affected_person_name` y `affected_phone` son opcionales; no se aprobó su obligatoriedad (D-34).
- `problem_date` es fecha y hora, editable en `PENDING`/`IN_PROGRESS`; valor inicial configurable (D-10).
- Nota Electrónica como identificador externo opcional; sin checkbox separado (D-09).
- Vínculo al creador por `user_id`; visualización/búsqueda por legajo (Q-014, D-20).
- Visibilidad según rol y área (D-03).
- La dirección se deriva de `Area.direction_id`; no se almacena una dirección afectada separada (D-29).
- Listado actual = `PENDING` + `IN_PROGRESS`; completados, cancelados y eliminados se listan por separado; filtros server-side por tipo, área, responsable, creador y fechas (D-35).
- La edición se limita a tickets abiertos; los cerrados son inmutables y se reabren o se eliminan/restauran (D-13, D-34).

### 7.5 Asignación

- Responsable opcional; asignación, reasignación y toma explícitas (D-05, D-12).
- `ADMINISTRATIVE` puede asignar; `AREA_MANAGER` puede asignar y reasignar; `AGENT` puede tomar tickets sin asignar.
- El responsable debe estar activo, pertenecer al área y tener rol `AGENT` o `AREA_MANAGER` (D-05).
- Iniciar no exige responsable (D-05).
- Creador, responsable y ejecutor son conceptos distintos.
- Toda asignación/reasignación/toma se registra con actor y fecha en historial y auditoría.
- Baja/cambio de área de un responsable con tickets activos: bloquear hasta reasignar o desasignar, con aviso explícito (D-06).

### 7.6 Ciclo de vida

- Cuatro estados: `PENDING`, `IN_PROGRESS`, `COMPLETED`, `CANCELLED` (D-01).
- La eliminación es una marca separada del estado (D-01).
- Transiciones aprobadas:
  - `PENDING` → `IN_PROGRESS`;
  - `IN_PROGRESS` → `COMPLETED`;
  - `PENDING` o `IN_PROGRESS` → `CANCELLED`;
  - `COMPLETED` o `CANCELLED` → reapertura hacia `PENDING` o `IN_PROGRESS`, elegido por ticket (D-30);
  - cualquier estado → eliminación lógica;
  - eliminado → restauración al estado previo.
- Completar exige solución y comprobante PDF válido (D-11, D-34).
- Cancelar y eliminar exigen motivo obligatorio (D-34).
- Los datos de cierre (`solved_at`, `solution`, `receipt`, `cancelled_at`, `cancellation_reason`) se persisten en `TicketClosure`, no en el ticket (D-30).
- Reapertura: motivo obligatorio; actor `SUPERUSER`, `ADMINISTRATIVE` o `AREA_MANAGER` según alcance; el responsable debe ser el mismo que al cierre; un ticket eliminado primero se restaura (D-30).
- Reapertura y restauración no conservan una asignación inválida; exigen reasignación o desasignación antes de completar la operación.
- Permisos aprobados: `ADMINISTRATIVE` inicia/reabre en su área y no completa/restaura; `AREA_MANAGER` inicia/completa/restaura/reabre en su área; `AGENT` inicia/completa/cancela los propios; `SUPERUSER` todo (D-31).
- Historial inmutable de transiciones.

Detalle en [`task-lifecycle.md`](./task-lifecycle.md).

### 7.7 Historial y auditoría

- Historial de estados visible en el detalle del ticket.
- Auditoría de cambios de negocio: altas, ediciones, asignaciones, transiciones, bajas, restauraciones, reaperturas, cambios de usuario/membresía/catálogo y configuración (D-22).
- No se auditan lecturas ni descargas (D-22).
- Se retiene toda la auditoría de negocio en principio (D-22).
- Consulta de auditoría sujeta a permiso y alcance (Q-002, Q-005).
- La auditoría de negocio se separa de los logs de seguridad; login, logout, intentos fallidos y bloqueos no se exponen por `/audit-events` (D-39).

### 7.8 Comprobantes

- Un archivo PDF por cierre (`TicketClosure`), no por ticket completo (D-11, D-30).
- Obligatorio para completar (D-11, D-34).
- Tamaño máximo configurable; validación en servidor (D-11).
- Almacenamiento local con metadatos y nombre relevante/único (Q-019).
- Descarga mediante endpoint autenticado y autorizado (Q-019).
- Sin URL pública directa (Q-019).
- No se reemplaza tras completar; se conserva con el cierre; los cierres anteriores y sus comprobantes se conservan al reabrir y volver a cerrar; escaneo opcional; purga física sólo con procedimiento auditado y explícito (D-25, D-30).

Detalle en [`reporting-and-files.md`](./reporting-and-files.md).

### 7.9 Estadísticas y panel

- “Tickets ingresados” por `created_at`.
- “Tickets completados” por `solved_at` del `TicketClosure`.
- “Tickets cancelados” por `cancelled_at` del `TicketClosure`.
- "Tickets eliminados" por eventos `DELETED` de `TicketTimelineEntry` para reportes históricos; `Ticket.deleted_at` sólo refleja la última eliminación, sujeto a `P-TICKET_VIEW_DELETED`.
- Panel "reportado hoy por estado" por `problem_date` (Q-010).
- Reportes históricos por evento con filtro explícito; el panel operativo excluye eliminados y los muestra aparte; se documenta el área actual (D-08).
- Períodos: hoy, rango, año actual y total histórico.
- Desglose por área y por tipo de ticket (`INCIDENT`/`TASK`) (D-32).
- Conteos escalares correctos (Q-011).
- Alcance por rol/área (D-03).
- Zona horaria configurable (D-14).
- No se agrega KPI de reaperturas: D-32 no lo aprobó.

Detalle en [`reporting-and-files.md`](./reporting-and-files.md).

### 7.10 Búsqueda, filtros y exportación

- Listado actual = `PENDING` + `IN_PROGRESS`; completados, cancelados y eliminados separados (D-35).
- Búsqueda por texto y filtros server-side por tipo, área, responsable, creador y rango de fechas (D-35).
- Paginación y orden server-side.
- Exportación de listados en CSV/XLSX y de reportes en PDF, síncrona con límite y aviso (D-24).
- Los filtros reemplazan los endpoints AJAX huérfanos (F-D-01).

### 7.11 Nota Electrónica

- Documento externo; se persiste su identificador (Q-007, D-09).
- No se almacena el documento ni su contenido.
- Formato y longitud configurables; visibilidad para roles operativos; sin checkbox separado (D-09).
- Obligatoriedad: no aprobada; se mantiene opcional.

### 7.12 Configuración del sistema

- Zona horaria `America/Argentina/Buenos_Aires` por defecto (D-14).
- El Administrador puede cambiarla (D-14).
- El cambio se audita (D-22).
- `/settings` sólo expone `timezone` en la primera versión; la configuración ampliada queda para versiones futuras (D-37).

### 7.13 Comportamiento funcional del frontend

Sin prescribir implementación React, la interfaz debe:

- reflejar permisos y alcance, pero no ser la única barrera;
- mostrar acciones válidas según estado y rol;
- informar errores de dominio de forma comprensible;
- permitir filtrar, ordenar y paginar listados;
- mostrar el historial del ticket;
- permitir descargar el comprobante sólo a usuarios autorizados;
- no exponer URLs directas a archivos;
- manejar la expiración de sesión de forma centralizada;
- distinguir `INCIDENT` y `TASK` en alta, edición, listados y detalle.

## 8. Reglas aprobadas RN-001 a RN-118

> Una regla es aprobada cuando deriva explícitamente de `docs/definitions.md` o de `docs/specification-definitions.md`. Las reglas aprobadas en la segunda ronda están en §9.

### 8.1 Transversales

| ID | Regla | Origen |
|---|---|---|
| RN-001 | Ninguna regla de negocio del legado es válida sin aprobación explícita. | `specification-definitions.md` |
| RN-002 | El modelo usa nombres en inglés; la entidad central es `Ticket`. | `specification-definitions.md` |
| RN-003 | Existen dos tipos de ticket: `INCIDENT` y `TASK`. | `specification-definitions.md` |
| RN-004 | No se migran datos históricos; el sistema se inicializa de cero. | Q-021 |
| RN-005 | La zona horaria por defecto es `America/Argentina/Buenos_Aires`; el Administrador puede cambiarla. | D-14 |
| RN-006 | Las operaciones usan concurrencia optimista con ETag/`If-Match`; conflicto `409`; `Idempotency-Key` opcional en altas. | D-27 |
| RN-007 | Se siembran roles, permisos y un `SUPERUSER` inicial para el desarrollador. | D-28 |

### 8.2 Autenticación y sesión

| ID | Regla | Origen |
|---|---|---|
| RN-010 | Toda operación se autoriza en el backend; se deniega por defecto. | Q-002 |
| RN-011 | La autenticación usa cookie `HttpOnly` y sesión de servidor. | D-07 |
| RN-012 | La sesión expira por inactividad deslizante a los 60 minutos. | D-07 |
| RN-013 | La baja, el cambio de rol/área y el blanqueo de contraseña invalidan sesiones activas. | D-07 |
| RN-014 | La reactivación exige un nuevo blanqueo de contraseña. | D-07 |
| RN-015 | Un usuario `INACTIVE` no puede iniciar sesión. | Q-001 |
| RN-016 | El correo es obligatorio y debe ser interno de la organización. | D-16 |
| RN-017 | El nombre de usuario y el correo son únicos. | Q-013 |
| RN-018 | El legajo es inmutable y único; identifica funcionalmente al usuario. | D-15, D-20 |
| RN-019 | La política de contraseñas es configurable; hay bloqueo temporal y rate limiting; MFA fuera de alcance. | D-23 |
| RN-020 | El blanqueo de contraseña usa la nueva contraseña y valida su coincidencia en servidor. | Q-009 |
| RN-021 | La autogestión del perfil localiza al usuario por su legajo, no por el texto ingresado en el login. | Q-006 |

### 8.3 Roles, permisos y membresías

| ID | Regla | Origen |
|---|---|---|
| RN-030 | Existen exactamente cuatro roles: `SUPERUSER`, `ADMINISTRATIVE`, `AREA_MANAGER`, `AGENT`. | D-02 |
| RN-031 | El `SUPERUSER` tiene alcance global. | D-03 |
| RN-032 | `ADMINISTRATIVE` y `AREA_MANAGER` operan por área. | D-03 |
| RN-033 | El `AGENT` ve tickets asignados y tickets sin asignar de su área. | D-03 |
| RN-034 | `ADMINISTRATIVE` puede crear, editar tickets creados por él y asignar. | D-05 |
| RN-035 | `AREA_MANAGER` puede crear, editar, asignar, reasignar, cancelar y eliminar tickets de su área. | D-05 |
| RN-036 | El `AGENT` puede tomar tickets sin asignar y transicionar los propios: iniciar, completar, cancelar. | D-12 |
| RN-037 | Un usuario puede tener una o más áreas mediante `UserAreaMembership`; un solo rol por área. | D-21 |
| RN-038 | El catálogo de roles es fijo; `P-ROLE_MANAGE` queda reservado y no se expone CRUD de roles. | D-17 |
| RN-039 | El `SUPERUSER` puede cambiar el rol de una membresía; el cambio se audita y rige de inmediato. | Q-024, D-21 |
| RN-040 | El responsable debe estar activo, pertenecer al área y tener rol `AGENT` o `AREA_MANAGER`. | D-05 |
| RN-041 | Iniciar un ticket no exige responsable. | D-05 |
| RN-042 | Creador, responsable y ejecutor de una transición son conceptos distintos. | Diseño aprobado por D-05 |
| RN-043 | Toda asignación, reasignación o toma registra actor, fecha y responsable anterior/nuevo. | Q-017, D-22 |

### 8.4 Áreas y direcciones

| ID | Regla | Origen |
|---|---|---|
| RN-050 | Sólo el `SUPERUSER` administra áreas y direcciones. | Q-004 |
| RN-051 | Una `Direction` agrupa áreas; cada `Area` tiene `direction_id`. | D-04 |
| RN-052 | Una dirección tiene PK subrogada, código editable y único, y `is_active`. | Q-018 |
| RN-053 | No se desactiva una dirección con áreas activas; no se desactiva un área con usuarios/tickets activos sin reasignación. | D-18 |
| RN-054 | El código de dirección puede cambiarse con auditoría; se puede reutilizar un código que no esté en uso activo. | D-19 |
| RN-055 | No se permite borrado físico que rompa referencias históricas. | Q-018, D-18 |

### 8.5 Tickets

| ID | Regla | Origen |
|---|---|---|
| RN-060 | El número visible del ticket es secuencial global, simple e inmutable. | D-26 |
| RN-061 | `problem_date` es fecha y hora, editable en `PENDING`/`IN_PROGRESS`; valor inicial configurable; futura sólo para `TASK`. | D-10 |
| RN-062 | La Nota Electrónica es un identificador externo opcional; no hay checkbox separado. | Q-007, D-09 |
| RN-063 | El ticket referencia al usuario por `id`; se muestra y busca por legajo. | Q-014, D-20 |
| RN-064 | Los tickets `COMPLETED` o `CANCELLED` son inmutables en ese estado. | D-13 |
| RN-065 | Los tickets `COMPLETED` o `CANCELLED` pueden reabrirse hacia `PENDING` o `IN_PROGRESS`, con motivo obligatorio y el mismo responsable del cierre; un ticket eliminado debe restaurarse primero (D-30). | D-13, D-30 |
| RN-066 | La visibilidad no es global por defecto; se define por rol y área. | Q-005, D-03 |
| RN-067 | Los tickets siguen visibles aunque su creador esté inactivo o haya cambiado de nombre. | Q-014 |
| RN-068 | La eliminación es una marca separada del estado de ciclo de vida. | D-01 |
| RN-069 | Se puede eliminar desde cualquier estado; restaurar conserva el estado previo. | Q-016, D-01 |
| RN-070 | Completar exige solución y un comprobante PDF válido. | D-11, D-34 |
| RN-071 | Cada cierre (`TicketClosure`) tiene como máximo un comprobante; un ticket puede tener varios cierres históricos. | Q-019, D-30 |
| RN-072 | El comprobante no se reemplaza tras completar; se conserva con su cierre; escaneo opcional; purga física sólo auditada y explícita. | D-25, D-30 |
| RN-073 | El comprobante se descarga mediante endpoint autenticado y autorizado; no hay URL pública directa. | Q-019 |
| RN-074 | El tamaño máximo del comprobante es configurable y se valida en servidor. | D-11 |
| RN-075 | La Nota Electrónica no se almacena como documento ni contenido. | Q-007 |

### 8.6 Ciclo de vida e historial

| ID | Regla | Origen |
|---|---|---|
| RN-080 | Los estados son `PENDING`, `IN_PROGRESS`, `COMPLETED`, `CANCELLED`. | D-01 |
| RN-081 | Las transiciones se validan en el dominio, no sólo en la interfaz. | Q-008 |
| RN-082 | Las transiciones aprobadas son las de §7.6; la reapertura se dirige a `PENDING` o `IN_PROGRESS` según se elija por ticket (D-30). | D-01, D-13, D-30 |
| RN-083 | Cada transición se registra en un historial inmutable con estado anterior, estado nuevo, actor y fecha. | Q-017 |
| RN-084 | Se auditan sólo cambios de negocio; no lecturas ni descargas. | D-22 |
| RN-085 | La auditoría registra actor, fecha y valores anteriores/nuevos o campos modificados. | Q-017, D-22 |
| RN-086 | La auditoría se retiene toda en principio. | D-22 |
| RN-087 | La consulta de auditoría requiere permiso y respeta el alcance. | Q-002, Q-005 |
| RN-088 | Una transición inválida se rechaza aunque el cliente la invoque directamente. | Q-008 |

### 8.7 Estadísticas y exportación

| ID | Regla | Origen |
|---|---|---|
| RN-090 | “Tickets ingresados” se cuenta por `created_at`. | Q-020 |
| RN-091 | “Tickets completados” se cuenta por `solved_at` del `TicketClosure`. | Q-020, D-30 |
| RN-092 | "Tickets cancelados" se cuenta por `cancelled_at` del `TicketClosure`; "eliminados" se cuenta por eventos `DELETED` de `TicketTimelineEntry` para historial. | Q-020, D-30 |
| RN-093 | El panel “de hoy” por estado usa `problem_date`. | Q-010 |
| RN-094 | Los reportes históricos son por evento con filtro explícito; el panel operativo excluye eliminados y los muestra aparte; se documenta el área actual. | D-08 |
| RN-095 | Los períodos son hoy, rango, año actual y total histórico. | Q-020 |
| RN-096 | Las estadísticas incluyen desglose por área y por tipo de ticket (`INCIDENT`/`TASK`). | D-08, D-32 |
| RN-097 | Los conteos se muestran como escalares correctos. | Q-011 |
| RN-098 | Las estadísticas respetan el alcance por rol/área. | Q-005, D-03 |
| RN-099 | Los listados se exportan en CSV/XLSX; los reportes en PDF; la exportación es síncrona con límite y aviso. | D-24 |
| RN-100 | La zona horaria de cálculo y presentación es configurable; default `America/Argentina/Buenos_Aires`. | D-14 |
| RN-101 | El cambio de configuración se audita. | D-22 |
| RN-102 | La dirección del ticket se deriva siempre de `Area.direction_id`; no existe `affected_direction_id`. | D-29 |
| RN-103 | La reapertura exige motivo, mismo responsable del cierre y destino `PENDING` o `IN_PROGRESS`; un ticket eliminado debe restaurarse primero. | D-30 |
| RN-104 | `TicketClosure` persiste `solved_at`, `solution`, `receipt`, `cancelled_at` y `cancellation_reason`; un ticket puede tener varios cierres históricos. | D-30 |
| RN-105 | `ADMINISTRATIVE` inicia/reabre en su área y no completa/restaura; `AREA_MANAGER` inicia/completa/restaura/reabre en su área; `AGENT` inicia/completa/cancela los propios. | D-31 |
| RN-106 | `INCIDENT` y `TASK` se diferencian por fecha futura y descripción funcional; pueden contabilizarse por separado. | D-32 |
| RN-107 | Sólo el `SUPERUSER` gestiona usuarios; el usuario edita nombre, apellido, correo y contraseña, no rol/área; los inactivos se excluyen de listados operativos. | D-33 |
| RN-108 | `type`, `description`, `area_id` y `problem_date` son obligatorios; el estado inicial es `PENDING`; `solution` y los motivos de cancelación/eliminación son obligatorios; sólo se editan tickets abiertos. | D-34 |
| RN-109 | El listado actual es `PENDING` + `IN_PROGRESS`; completados, cancelados y eliminados se listan por separado; filtros server-side por tipo, área, responsable, creador y fechas. | D-35 |
| RN-110 | Se bloquea la baja, auto-baja y cambio de rol del último `SUPERUSER` activo. | D-36 |
| RN-111 | `/settings` sólo expone `timezone` en la primera versión. | D-37 |
| RN-112 | El login acepta usuario o correo; se registra `last_login_at`. | D-38 |
| RN-113 | La auditoría de negocio se separa de los logs de seguridad; los eventos de seguridad no se exponen por `/audit-events`. | D-39 |
| RN-114 | El seed crea un `SUPERUSER` inicial sin contraseña utilizable; la cuenta debe establecer contraseña mediante blanqueo/activación antes de operar. | D-28 |
| RN-115 | El responsable histórico de un cierre se reconstruye desde `TicketTimelineEntry`; no se duplica en `TicketClosure`. | Revisión 4 |
| RN-116 | La reapertura y la restauración no pueden conservar una asignación inválida; exigen reasignación o desasignación. | Revisión 4 |
| RN-117 | La eliminación y la restauración se registran como eventos históricos; los campos actuales de `Ticket` sólo reflejan la última operación. | Revisión 4 |
| RN-118 | `AGENT` puede consultar auditoría de sus tickets asignados; no accede a auditoría global ni de área. | Revisión 4 |

## 9. Reglas aprobadas en la segunda ronda PR-001 a PR-017

Estas reglas quedaron aprobadas por las decisiones D-29 a D-39 y se incorporan a la especificación.

| ID | Regla aprobada | Decisión |
|---|---|---|
| PR-001 | Todo ticket nace en `PENDING`. | D-34 |
| PR-002 | Son obligatorios `type`, `description`, `area_id` y `problem_date`; afectado y celular son opcionales. | D-34 |
| PR-003 | Completar exige `solution` además del comprobante. | D-34 |
| PR-004 | Cancelar y eliminar exigen motivo. | D-34 |
| PR-005 | Sólo se editan tickets `PENDING`/`IN_PROGRESS`; la edición no altera estado, creador, `created_at`, cierre, comprobante, cancelación ni eliminación. | D-34 |
| PR-006 | `ADMINISTRATIVE` inicia/reabre en su área y no completa/restaura; `AREA_MANAGER` inicia/completa/restaura/reabre en su área. | D-31 |
| PR-007 | `AREA_MANAGER` puede restaurar y reabrir tickets de su área; `ADMINISTRATIVE` no restaura. | D-31 |
| PR-008 | La reapertura la realiza `SUPERUSER`, `ADMINISTRATIVE` o `AREA_MANAGER` según alcance, con motivo, destino `PENDING`/`IN_PROGRESS`, mismo responsable del cierre; los datos de cierre se conservan en `TicketClosure`; un eliminado se restaura primero. | D-30 |
| PR-009 | El ticket usa `area_id` y no conserva una dirección afectada separada; la dirección se deriva del área. | D-29 |
| PR-010 | `INCIDENT` y `TASK` sólo se diferencian por la fecha futura y por su descripción funcional; pueden contabilizarse por separado. | D-32 |
| PR-011 | Listado actual = `PENDING` + `IN_PROGRESS`; completados, cancelados y eliminados tienen listados separados; filtros server-side por tipo, área, responsable, creador y fechas. | D-35 |
| PR-012 | Sólo el `SUPERUSER` crea/edita usuarios y membresías; el usuario edita nombre, apellido, correo y contraseña; no cambia su propio rol/área. | D-33 |
| PR-013 | Usuarios inactivos se excluyen de listados operativos; el login acepta usuario o correo; se registra `last_login_at`. | D-33/D-38 |
| PR-014 | No se puede dar de baja, auto-dar de baja ni quitar el rol al último `SUPERUSER` activo. | D-36 |
| PR-015 | La zona horaria se administra desde `/settings`; en la primera versión sólo se expone `timezone`. | D-37 |
| PR-016 | Los eventos de seguridad (login, logout, intentos fallidos) se registran en logs separados de la auditoría de negocio y no se exponen por `/audit-events`. | D-39 |
| PR-017 | Las estadísticas pueden filtrarse y desglosarse por tipo de ticket; no se agrega KPI de reaperturas. | D-32 |

## 10. Trazabilidad de las definiciones Q-001 a Q-026

| Q | Definición original | Dónde se refleja | Estado |
|---|---|---|---|
| Q-001 | La baja impide login e invalida sesiones y/o contraseña. | RN-013, RN-015; D-07. | Resuelta: se invalidan sesiones y se exige nuevo blanqueo al reactivar. |
| Q-002 | Autorización obligatoria en backend. | RN-010, RN-081, RN-088. | Resuelta. |
| Q-003 | Roles SUPERUSER y Administrativo; se prevén Agente y Responsable; índices reiniciables. | RN-030 a RN-039. | Resuelta por D-02/D-17/D-21. |
| Q-004 | Sólo SUPERUSER administra áreas/direcciones; área depende de dirección. | RN-050, RN-051, RN-102. | Resuelta por D-04/D-29. |
| Q-005 | Visibilidad por rol, a definir con roles nuevos. | RN-031 a RN-033, RN-066, RN-098. | Resuelta por D-03. |
| Q-006 | Autogestión por legajo. | RN-021. | Resuelta. |
| Q-007 | Nota Electrónica = documento externo; se guarda identificador. | RN-062, RN-075. | Resuelta por D-09. |
| Q-008 | Máquina de estados validada en dominio. | RN-081, RN-088. | Resuelta. |
| Q-009 | Blanqueo usa nueva contraseña y valida coincidencia en servidor. | RN-020. | Resuelta. |
| Q-010 | “Hoy” por estado usa `DATE(fechaProblema) = hoy`. | RN-093. | Resuelta; tensión con Q-020 resuelta por D-08. |
| Q-011 | La vista imprime el escalar. | RN-097. | Resuelta. |
| Q-012 | Expiración deslizante, centralizada; duración a definir. | RN-012. | Resuelta: 60 minutos (D-07). |
| Q-013 | Usuario y correo únicos con constraints. | RN-016, RN-017. | Resuelta; correo obligatorio por D-16. |
| Q-014 | Tickets visibles; vínculo por legajo. | RN-063, RN-067. | Resuelta por D-20. |
| Q-015 | Asignación existe; funcionalidad nueva. | RN-040 a RN-043. | Resuelta por D-05. |
| Q-016 | Eliminar desde cualquier estado; restaurar conservando estado previo. | RN-068, RN-069. | Resuelta por D-01. |
| Q-017 | Historial de estados y auditoría. | RN-083 a RN-087. | Resuelta por D-22. |
| Q-018 | Direcciones con PK subrogada, código editable único y `active`. | RN-052, RN-054, RN-055. | Resuelta por D-18/D-19. |
| Q-019 | Un archivo por ticket, local, validado, endpoint autenticado, nombre relevante. | RN-071 a RN-074. | Resuelta por D-11/D-25/D-30: un comprobante por cierre. |
| Q-020 | Completados por solución; ingresados por creación; más estadísticas. | RN-090 a RN-098. | Resuelta por D-08. |
| Q-021 | No se migran datos históricos. | RN-004. | Resuelta. |
| Q-022 | Recuperar funcionalidades del modal conforme a definiciones. | §6.3; D-02/D-05/D-09/D-33/D-34. | Resuelta en alcance. |
| Q-023 | `fechaProblema` declarada y editable; `fechaCreada` timestamp. | RN-061. | Resuelta por D-10. |
| Q-024 | SUPERUSER puede cambiar rol. | RN-039. | Resuelta; rol por área por D-21. |
| Q-025 | Recuperar filtros/agentes conforme a definiciones. | §7.10, RN-109. | Resuelta por D-35. |
| Q-026 | Zona horaria local fija. | RN-005, RN-100. | Resuelta por D-14. |

## 11. Disposición de las reglas heredadas BR-001 a BR-054

Leyenda:

- **A** = aprobada explícitamente por `definitions.md` o `specification-definitions.md`.
- **P** = pendiente; no aprobada; requiere decisión.
- **R** = reemplazada por una decisión aprobada.
- **D** = descartada por contradecir una decisión aprobada.
- **S** = control técnico o de seguridad, no regla de negocio.

| BR | Disposición | Regla nueva / observación |
|---|---|---|
| BR-001 | A | RN-112: login por usuario o correo (D-38). |
| BR-002 | S | Hash de contraseñas: control de seguridad, no regla de negocio. |
| BR-003 | R | La sesión no es fuente de autorización; el backend revalida identidad, rol y alcance (Q-002). |
| BR-004 | A | RN-112: se registra `last_login_at` (D-38). |
| BR-005 | A | RN-012: 60 minutos deslizantes (D-07). |
| BR-006 | S | Logout: control de seguridad. |
| BR-007 | A | RN-015: usuario inactivo no inicia sesión (Q-001). |
| BR-008 | A | RN-031: Administrador global (D-03). |
| BR-009 | A | RN-032/RN-034: Administrativo por área, crea/edita propias/asigna (D-03/D-05). |
| BR-010 | A | RN-107: sólo el `SUPERUSER` gestiona usuarios (D-33). |
| BR-011 | A | RN-102: la dirección se deriva del área; los roles operativos ven la dirección de sus áreas (D-29/D-03). |
| BR-012 | A | RN-050: sólo SUPERUSER administra áreas (Q-004). |
| BR-013 | A | RN-010: autorización backend (Q-002). |
| BR-014 | P | Campos obligatorios de usuario no aprobados, salvo correo (D-16) y legajo (D-15); D-33. |
| BR-015 | A | RN-018: legajo único e inmutable (D-15/D-20). |
| BR-016 | A | RN-017: usuario y correo únicos (Q-013). |
| BR-017 | S | Hash de contraseñas: control de seguridad. |
| BR-018 | A | RN-015/RN-068: baja lógica y estado separado del rol (Q-001/D-01). La parte `idRol2 = NULL` se reemplaza. |
| BR-019 | A | RN-014: reactivación con nuevo blanqueo (D-07). |
| BR-020 | A | RN-020: blanqueo administrativo (Q-009). |
| BR-021 | A | RN-021: autogestión por legajo (Q-006). |
| BR-022 | A | RN-107: el usuario no cambia su propio rol/área (D-33). |
| BR-023 | A | RN-107: inactivos excluidos de listados operativos (D-33). |
| BR-024 | A | RN-108: `type`, `description`, `area_id` y `problem_date` obligatorios (D-34). |
| BR-025 | A | RN-108: estado inicial `PENDING`; `problem_date` editable (D-10/D-34). |
| BR-026 | A | RN-066: visibilidad por rol/área (D-03). |
| BR-027 | A | RN-041: iniciar no exige responsable (D-05). |
| BR-028 | A | RN-070: comprobante y solución obligatorios (D-11/D-34). |
| BR-029 | A | RN-035/RN-036/RN-108: cancelación por Area Manager/Agent propio, con motivo obligatorio (D-05/D-12/D-34). |
| BR-030 | A | RN-068/RN-069/RN-108: eliminación lógica restaurable, con motivo obligatorio (Q-016/D-01/D-34). |
| BR-031 | A | RN-064: cerrados inmutables (D-13). |
| BR-032 | A | RN-080/RN-082: máquina de estados (Q-008/D-01/D-13). |
| BR-033 | D | Descartada: D-13 permite reapertura. |
| BR-034 | A | RN-109: listado actual `PENDING` + `IN_PROGRESS` (D-35). |
| BR-035 | A | RN-109: completados separados y filtrables por fecha (D-35). |
| BR-036 | A | RN-109: cancelados separados (D-35). |
| BR-037 | A | RN-109: eliminados separados (D-35). |
| BR-038 | A | RN-062: Nota Electrónica como identificador externo (Q-007/D-09). |
| BR-039 | A | RN-071 a RN-074: comprobante (Q-019/D-11/D-25). |
| BR-040 | A | RN-063: vínculo por identidad/legajo (Q-014/D-20). |
| BR-041 | A | RN-067: tickets visibles tras baja/renombre (Q-014). |
| BR-042 | A | RN-108: sólo se editan tickets abiertos y no se alteran estado, creador, cierre, comprobante, cancelación ni eliminación (D-34). |
| BR-043 | A | RN-108: `area_id` obligatorio (D-34). |
| BR-044 | R | Reemplazada por D-29: no hay dirección afectada separada; la dirección se deriva de `Area.direction_id`. |
| BR-045 | A | RN-052/RN-054: código de dirección único/editable/reutilizable (Q-018/D-19). |
| BR-046 | A | RN-053/RN-055: baja lógica y no borrado físico (Q-018/D-18). |
| BR-047 | A | RN-053: áreas con `is_active` (D-18). |
| BR-048 | A | RN-093/RN-094: panel y criterios (Q-010/Q-011/D-08). |
| BR-049 | A | RN-090 a RN-098: estadísticas (Q-020/D-08). |
| BR-050 | A | RN-096: desglose por área actual (D-08). |
| BR-051 | A | RN-093: comparación por fecha de `problem_date` (Q-010). |
| BR-052 | A | RN-099: exportación CSV/XLSX/PDF (D-24). |
| BR-053 | A | RN-083 a RN-087: historial/auditoría (Q-017/D-22). |
| BR-054 | A | RN-085: fechas y actores en historial/auditoría (Q-017/D-22). |

## 12. Contradicciones y ambigüedades

| ID | Contradicción / ambigüedad | Tratamiento |
|---|---|---|
| C-01 | “Área operativa” vs “área dependiente de dirección”. | Resuelta por D-04: son la misma entidad. |
| C-02 | Eliminación como estado `Eliminado` vs restauración. | Resuelta por D-01: cuatro estados + marca de eliminación. |
| C-03 | Q-010 (hoy por `problem_date`) vs Q-020 (completados por `solved_at`). | Resuelta por D-08: panel operativo vs reporte histórico por evento. |
| C-04 | “Sesiones y/o contraseña” en Q-001. | Resuelta por D-07: invalidar sesiones y exigir nuevo blanqueo al reactivar. |
| C-05 | Correo opcional vs login por correo y unicidad. | Resuelta por D-16: correo obligatorio e interno. |
| C-06 | Roles nuevos vs catálogo legado. | Resuelta por D-02: cuatro roles fijos. |
| C-07 | Un usuario, un área/rol vs multi-área. | Resuelta por D-21: multi-área con un rol por área. |
| C-08 | Nota Electrónica externa vs texto libre. | Resuelta por D-09: identificador externo opcional. |
| C-09 | Reapertura prohibida vs quejas. | Resuelta por D-13/D-30: existe reapertura con destino `PENDING`/`IN_PROGRESS`, motivo y mismas reglas de responsable. |
| C-10 | Iniciar exigía responsable vs D-05. | Resuelta por D-05: iniciar no exige responsable. |
| C-11 | Agente sólo ejecuta vs D-12 permite cancelar propio. | Resuelta por D-12. |
| C-12 | Sólo SUPERUSER gestiona usuarios vs D-05 global. | Resuelta por D-33: sólo el `SUPERUSER` gestiona usuarios. |
| C-13 | Roles dinámicos vs catálogo fijo. | Resuelta por D-17: catálogo fijo. |
| C-14 | Auditoría de descargas vs sólo negocio. | Resuelta por D-22: no se auditan descargas. |
| C-15 | Zona horaria fija vs configurable. | Resuelta por D-14: default configurable. |
| C-16 | Código de dirección no reutilizable vs reutilizable. | Resuelta por D-19: reutilizable si no está activo. |
| C-17 | Dirección del ticket: área vs afectado. | Resuelta por D-29: se deriva de `Area.direction_id`; no hay dirección afectada separada. |
| C-18 | Reapertura: actor, destino, motivo y datos de cierre. | Resuelta por D-30: destino `PENDING`/`IN_PROGRESS`, motivo obligatorio, mismo responsable y `TicketClosure` para datos de cierre. |
| C-19 | Permisos de inicio/complete/restore/reopen para Administrativo y Responsable. | Resuelta por D-31. |
| C-20 | Reglas específicas de `INCIDENT` vs `TASK` más allá de fecha futura. | Resuelta por D-32: fecha futura, descripción funcional y estadísticas separadas. |
| C-21 | Gestión de usuarios, autogestión de rol e inactivos. | Resuelta por D-33. |
| C-22 | Campos obligatorios, estado inicial, solución y motivos. | Resuelta por D-34. |
| C-23 | Listados por estado y filtros. | Resuelta por D-35. |
| C-24 | Protección del último Administrador. | Resuelta por D-36: se bloquea baja, auto-baja y cambio de rol del último `SUPERUSER` activo. |
| C-25 | Configuración de zona horaria y parámetros. | Resuelta por D-37: `/settings` sólo `timezone` en la primera versión. |
| C-26 | Identificador de login y último acceso. | Resuelta por D-38: usuario o correo; `last_login_at`. |
| C-27 | Auditoría de negocio vs logs de seguridad. | Resuelta por D-39: auditoría separada; logs de seguridad no se exponen por `/audit-events`. |
| C-28 | “Ninguna regla del legado es válida sin aprobación” vs reglas operativas necesarias. | Se clasifican como aprobadas, pendientes o controles técnicos; §11. |

## 13. Fuera de alcance

Salvo definición expresa en contrario, quedan fuera de esta fase y de la primera implementación:

- prioridad, severidad, categoría y SLA de tickets;
- comentarios o conversaciones sobre el ticket;
- adjuntos múltiples;
- notificaciones por correo o en aplicación;
- registro público de usuarios;
- recuperación de contraseña por correo;
- MFA, SSO o autenticación federada;
- multi-tenant, multi-idioma y múltiples organizaciones;
- integración con el sistema externo de Nota Electrónica más allá del identificador;
- migración de datos históricos (Q-021);
- aplicación móvil nativa;
- tiempo real o websockets;
- reportes programados;
- purga física de tickets;
- gestión dinámica de roles/permisos (D-17);
- rol “Consulta” (D-02).

## 14. Criterios de aceptación funcionales

La Fase 1, revisión 4, se considera aceptada cuando:

1. cada decisión D-01 a D-39 está trazada sin contradicciones;
2. la entidad central es `Ticket` y existen `INCIDENT` y `TASK`;
3. el modelo, campos, estados, roles, permisos y eventos están en inglés;
4. ninguna regla del legado se presenta como definitiva sin aprobación explícita;
5. los roles aprobados son exactamente cuatro y el rol `SUPERUSER` reemplaza al código `ADMIN`;
6. la visibilidad del `AGENT` incluye asignados y sin asignar de su área;
7. la reapertura, el cierre y los permisos de D-30/D-31 están modelados;
8. la pertenencia multi-área con un rol por área está modelada;
9. las decisiones D-29 a D-39 están incorporadas y no quedan P0/P1 abiertas;
10. no se creó código, proyecto, dependencia ni migración.

## 15. Riesgos funcionales

| Riesgo | Impacto | Mitigación |
|---|---|---|
| Sin migración de históricos (Q-021) | Medio: pérdida de continuidad operativa. | Confirmar y comunicar alcance. |
| `TicketClosure` agrega una entidad y cambia el modelo de comprobantes | Medio: mayor complejidad de consultas y migraciones futuras. | Implementar la relación ticket–cierres y conservar comprobantes históricos. |
| Obligatoriedad de campos de usuario no detallada (BR-014) | Bajo/medio: validaciones de alta de usuario. | Definir en Fase 2 sin bloquear el modelo. |
| Requisito de contraseña actual para cambio propio | Bajo: control de seguridad. | Tratarlo como control técnico en Fase 2. |
| Retención y acceso a logs de seguridad (D-39) | Bajo/medio: operación y auditoría. | Definir política operativa de logs. |
| Edición de `username` no definida | Bajo: gestión de usuarios. | Mantener `username` estable y resolver en Fase 2. |
| Visibilidad de eliminados para `AGENT` | Bajo: listados. | Aplicar D-03/D-35; el `AGENT` no recibe listado de eliminados en la primera versión. |

## 16. Decisiones

El registro completo está en [`open-decisions.md`](./open-decisions.md). Las decisiones D-01 a D-39 están aprobadas e incorporadas. No quedan decisiones P0/P1/P2 abiertas; la Fase 2 puede iniciarse cuando esta revisión sea aprobada.
