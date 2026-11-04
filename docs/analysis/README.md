# Análisis del sistema legado — índice

Documentación de **Fase 0** (análisis funcional y técnico del sistema PHP original).
No se modificó código, base de datos ni archivos del sistema; sólo se generaron estos documentos.

| Documento | Contenido |
|---|---|
| [`business-overview.md`](./business-overview.md) | Qué es el sistema, contexto, módulos, entidades, ciclo de vida y alcance real. |
| [`actors-and-permissions.md`](./actors-and-permissions.md) | Actores, roles, matriz de permisos y cómo se implementan (y dónde fallan). |
| [`workflows.md`](./workflows.md) | Flujos funcionales paso a paso, con SQL, validaciones y desviaciones. |
| [`domain-model.md`](./domain-model.md) | Entidades, atributos, relaciones, integridad y ambigüedades del modelo. |
| [`current-functionalities.md`](./current-functionalities.md) | Inventario de funcionalidades (activas, parciales, sólo endpoint y huérfanas). |
| [`business-rules.md`](./business-rules.md) | Catálogo de 54 reglas de negocio con evidencia y nivel de certeza. |
| [`legacy-architecture.md`](./legacy-architecture.md) | Arquitectura, acoplamientos, deuda técnica, riesgos de migración y dependencias. |
| [`open-questions.md`](./open-questions.md) | 26 preguntas abiertas, contradicciones, funcionalidades incompletas y código sin referencias. |

## Lectura recomendada para iniciar la Fase 1

1. `open-questions.md` → resolver primero las preguntas **bloqueantes** (Q-001, Q-002, Q-003, Q-005, Q-014, Q-015, Q-021).
2. `business-rules.md` → fuente de comportamiento a preservar.
3. `domain-model.md` + `workflows.md` → insumo para el diseño de la API y del modelo de datos.
4. `legacy-architecture.md` §9 → qué **no** trasladar.

## Estado

- Código fuente: **sin modificar**.
- Base de datos: **sin modificar**.
- Proyectos ASP.NET Core / React: **no creados** (fuera del alcance de esta fase).
- Implementación: **no iniciada**, a la espera de aprobación humana.
