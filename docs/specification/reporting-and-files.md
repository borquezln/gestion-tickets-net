# Reportes, estadísticas y archivos

> **Fase 1 — Revisión 4.** Criterios de D-08, D-11, D-14, D-22, D-24, D-25, D-30, D-32, D-34, D-35, D-37 y D-39.
> Los indicadores se definen por población y fecha; no se mezclan “hoy por problema” con “completados por solución”.

## 1. Decisiones aplicables

| Decisión | Efecto |
|---|---|
| D-08 | Reportes históricos por evento con filtro explícito; panel operativo excluye eliminados y los muestra aparte; documentar el área actual. |
| D-11 | Comprobante PDF obligatorio; tamaño máximo configurable; validación en servidor. |
| D-14 | Zona horaria configurable; default `America/Argentina/Buenos_Aires`. |
| D-22 | Auditoría sólo de cambios de negocio; no se auditan descargas. |
| D-24 | CSV/XLSX para listados; PDF para reportes; exportación síncrona con límite y aviso. |
| D-25 | No reemplazar comprobante tras completar; retener; escaneo opcional; purga física auditada. |
| D-30 | Los datos de cierre viven en `TicketClosure`; un ticket puede tener varios cierres y comprobantes históricos. |
| D-32 | `INCIDENT` y `TASK` pueden contabilizarse por separado. |
| D-34 | `solution` obligatoria; motivos de cancelación/eliminación obligatorios. |
| D-35 | Listados separados por estado; filtros por tipo, área, responsable, creador y fechas. |
| D-37 | `/settings` sólo expone `timezone` en la primera versión. |
| D-39 | Auditoría de negocio separada de logs de seguridad. |

## 2. Principios de reportes

1. Cada KPI declara **población** y **fecha**.
2. El panel operativo y el reporte histórico pueden usar criterios distintos, pero deben estar rotulados.
3. Los eliminados no se mezclan con los operativos; se muestran aparte.
4. El área informada es la **actual** del ticket, no una copia histórica (D-08).
5. Los cálculos usan la zona horaria configurada.
6. Los conteos se devuelven como escalares, no como arrays indexados (Q-011).
7. Las exportaciones respetan el alcance del usuario.
8. Los cierres se leen desde `TicketClosure`; un ticket puede aportar más de un cierre histórico.

## 3. KPIs

### 3.1 Panel operativo

| KPI | Población | Fecha | Regla |
|---|---|---|---|
| `pending` | Tickets `PENDING` no eliminados | — | Conteo actual. |
| `in_progress` | Tickets `IN_PROGRESS` no eliminados | — | Conteo actual. |
| `completed` | Tickets `COMPLETED` no eliminados | — | Conteo actual; el detalle usa el último `TicketClosure` `COMPLETED`. |
| `cancelled` | Tickets `CANCELLED` no eliminados | — | Conteo actual; el detalle usa el último `TicketClosure` `CANCELLED`. |
| `reported_today` | Tickets no eliminados, agrupados por estado | `problem_date` = hoy | Panel “reportado hoy” (Q-010, D-08). |
| `deleted` | Tickets eliminados | `deleted_at` | Se muestran aparte; no se suman a los operativos; sujeto a `P-TICKET_VIEW_DELETED`. |

### 3.2 Reportes históricos por evento

| KPI | Población | Fecha |
|---|---|---|
| `created` | Tickets creados, incluidos luego eliminados si el filtro lo indica y el rol tiene `P-TICKET_VIEW_DELETED` | `Ticket.created_at` |
| `solved` | Cierres `COMPLETED` | `TicketClosure.solved_at` |
| `cancelled` | Cierres `CANCELLED` | `TicketClosure.cancelled_at` |
| `deleted` | Eventos `DELETED` de `TicketTimelineEntry`; sujeto a `P-TICKET_VIEW_DELETED` | `TicketTimelineEntry.occurred_at` |

El reporte histórico debe declarar si incluye o excluye eliminados. D-08 exige filtro explícito. Un ticket reabierto y vuelto a cerrar aporta un nuevo cierre al reporte histórico; los cierres anteriores se conservan.

Las fuentes históricas son: creación por `Ticket.created_at`; resolución por `TicketClosure.solved_at`; cancelación por `TicketClosure.cancelled_at`; eliminación por eventos `DELETED` de `TicketTimelineEntry`; restauración por eventos `RESTORED`. Los campos actuales de `Ticket` sólo reflejan la última operación.

### 3.3 Períodos

- `today`
- `range` (`date_from`, `date_to`)
- `year` (año actual)
- `all` (total histórico)

### 3.4 Agrupaciones

- por estado;
- por área actual;
- por fecha;
- por tipo de ticket (`INCIDENT`/`TASK`) (D-32).

No se agrega KPI de reaperturas: D-32 no lo aprobó. Tampoco se agregan KPIs de prioridad, SLA o categoría sin decisión.

## 4. Panel operativo vs reporte histórico

### 4.1 Panel operativo

- Excluye tickets eliminados.
- Muestra los eliminados en una sección aparte sólo para roles con `P-TICKET_VIEW_DELETED`: `SUPERUSER` global; `ADMINISTRATIVE` y `AREA_MANAGER` en su área; `AGENT` no (D-03/D-35).
- “Reportado hoy” usa `problem_date` y la zona configurada.
- No usa `created_at` para “hoy”.
- El listado actual es `PENDING` + `IN_PROGRESS`; completados y cancelados se consultan aparte (D-35).

### 4.2 Reporte histórico

- Se basa en eventos (`Ticket.created_at`, `TicketClosure.solved_at`, `TicketClosure.cancelled_at`, `Ticket.deleted_at`).
- Permite incluir o excluir eliminados con un filtro explícito, sujeto a `P-TICKET_VIEW_DELETED`.
- Documenta que el área es la actual.
- No reutiliza el criterio “hoy” del panel.

## 5. Alcance de estadísticas

| Rol | Alcance |
|---|---|
| `SUPERUSER` | Global. |
| `ADMINISTRATIVE` | Sus áreas. |
| `AREA_MANAGER` | Sus áreas. |
| `AGENT` | Sin acceso. |

El backend aplica el alcance antes de calcular; no se filtra sólo en la UI.

## 6. Zona horaria

- Almacenamiento en UTC.
- Presentación y agrupación por día en la zona configurada.
- Default `America/Argentina/Buenos_Aires` (D-14).
- El Administrador puede cambiarla; el cambio se audita.
- `/settings` sólo expone `timezone` en la primera versión (D-37).

## 7. Exportación

### 7.1 Formatos

| Uso | Formato |
|---|---|
| Listados de tickets/usuarios/áreas/direcciones | CSV o XLSX (D-24). |
| Reportes de estadísticas | PDF (D-24). |
| Comprobante de cierre | PDF (D-11/D-30). |

### 7.2 Modo

- Exportación síncrona.
- Límite configurable de filas/tamaño.
- Si se supera, `413` con aviso; no se genera asíncrona en la primera versión (D-24).

### 7.3 Alcance y auditoría

- La exportación respeta permisos y alcance; incluir eliminados exige `P-TICKET_VIEW_DELETED`.
- No se audita la exportación ni la descarga (D-22).
- No se exponen URLs públicas.
- Los listados respetan los filtros y vistas de D-35.

## 8. Comprobantes

### 8.1 Obligatoriedad

- Un comprobante PDF es obligatorio para cada cierre `COMPLETED` (D-11/D-30).
- La solución es obligatoria y se guarda en el mismo `TicketClosure` (D-34).
- Un cierre `CANCELLED` exige motivo, no comprobante.

### 8.2 Validación

- Tipo `application/pdf`.
- Tamaño máximo configurable; validación en servidor.
- Verificación de firma/contenido básica si se define en Fase 2.
- Rechazo con `415` o `413`.

### 8.3 Almacenamiento

- Local, con nombre único y metadatos.
- `storage_key` no expuesto como URL pública.
- `checksum` para integridad.
- Descarga mediante endpoint autenticado.
- El comprobante se asocia a `TicketClosure`, no directamente al ticket.

### 8.4 Ciclo de vida del archivo

- Se adjunta al completar un cierre.
- No se reemplaza tras completar (D-25).
- Se conserva con su cierre.
- Al reabrir y volver a cerrar, el nuevo cierre puede tener un nuevo comprobante; el anterior se conserva.
- El escaneo antivirus es opcional (D-25).
- La purga física sólo se realiza con procedimiento auditado y explícito (D-25).

### 8.5 Eliminación del ticket

- La eliminación lógica del ticket no borra los comprobantes.
- La restauración conserva los comprobantes.
- La purga física del ticket/archivo no está aprobada como operación normal.

## 9. Historial y auditoría en reportes

- El historial de estados es visible en el detalle.
- La auditoría de negocio se consulta por separado.
- No se incluyen lecturas ni descargas (D-22).
- Los logs de seguridad se separan y no se exponen por `/audit-events` (D-39).

## 10. Decisiones incorporadas

No quedan decisiones pendientes que afecten reportes. D-30, D-32, D-34, D-35, D-37 y D-39 están incorporadas; los riesgos residuales están en [`open-decisions.md`](./open-decisions.md).

## 11. Trazabilidad

| Decisión | Dónde se refleja |
|---|---|
| D-08 | §3, §4, §5. |
| D-11 | §8.1, §8.2. |
| D-14 | §6. |
| D-22 | §7.3, §9. |
| D-24 | §7. |
| D-25 | §8.4, §8.5. |
| D-30 | §1, §2, §3.2, §8. |
| D-32 | §3.4. |
| D-34 | §3.2, §8.1. |
| D-35 | §3.1, §4.1, §7.3. |
| D-37 | §6. |
| D-39 | §9. |
| Q-010 | §3.1. |
| Q-011 | §2. |
| Q-019 | §8. |
| Q-020 | §3.2. |
| Q-026 | §6. |
