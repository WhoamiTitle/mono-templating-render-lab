# BenchmarkRun Domain

Aggregate root for benchmark series (N iterations) with summary metrics:

- `avg_ms`
- `min_ms`
- `max_ms`
- `p95_ms`

Summary metrics are provided by client benchmark and validated/persisted on backend.

`RenderRun` remains a single render execution, while `BenchmarkRun` models a series.
