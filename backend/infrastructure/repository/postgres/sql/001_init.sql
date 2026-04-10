CREATE TABLE IF NOT EXISTS users (
    user_id TEXT PRIMARY KEY,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    status TEXT NOT NULL,
    created_at TIMESTAMPTZ NOT NULL,
    updated_at TIMESTAMPTZ NOT NULL,
    last_login_at TIMESTAMPTZ NULL
);

CREATE INDEX IF NOT EXISTS users_email_idx ON users (email);

CREATE TABLE IF NOT EXISTS templates (
    template_id TEXT PRIMARY KEY,
    owner_id TEXT NOT NULL REFERENCES users (user_id),
    name TEXT NOT NULL,
    engine_type TEXT NOT NULL,
    template_body TEXT NOT NULL,
    created_at TIMESTAMPTZ NOT NULL,
    updated_at TIMESTAMPTZ NOT NULL,
    is_active BOOLEAN NOT NULL
);

CREATE INDEX IF NOT EXISTS templates_owner_updated_idx
    ON templates (owner_id, updated_at DESC, template_id ASC);

CREATE INDEX IF NOT EXISTS templates_owner_engine_idx
    ON templates (owner_id, engine_type);

CREATE TABLE IF NOT EXISTS render_runs (
    run_id TEXT PRIMARY KEY,
    template_id TEXT NOT NULL REFERENCES templates (template_id),
    owner_id TEXT NOT NULL REFERENCES users (user_id),
    engine_type TEXT NOT NULL,
    template_body_snapshot TEXT NOT NULL,
    context_json JSONB NOT NULL,
    started_at TIMESTAMPTZ NOT NULL,
    finished_at TIMESTAMPTZ NULL,
    status TEXT NOT NULL,
    duration_ms INTEGER NULL,
    output_text TEXT NULL,
    error_code TEXT NULL,
    error_message TEXT NULL
);

CREATE INDEX IF NOT EXISTS render_runs_owner_started_idx
    ON render_runs (owner_id, started_at DESC, run_id ASC);

CREATE INDEX IF NOT EXISTS render_runs_owner_status_idx
    ON render_runs (owner_id, status);

CREATE INDEX IF NOT EXISTS render_runs_template_idx
    ON render_runs (template_id);

CREATE TABLE IF NOT EXISTS benchmark_runs (
    benchmark_run_id TEXT PRIMARY KEY,
    owner_id TEXT NOT NULL REFERENCES users (user_id),
    template_id TEXT NOT NULL REFERENCES templates (template_id),
    engine_type TEXT NOT NULL,
    context_json JSONB NOT NULL,
    iterations_n INTEGER NOT NULL,
    started_at TIMESTAMPTZ NOT NULL,
    finished_at TIMESTAMPTZ NULL,
    status TEXT NOT NULL,
    samples_ms JSONB NOT NULL DEFAULT '[]'::jsonb,
    avg_ms DOUBLE PRECISION NULL,
    min_ms INTEGER NULL,
    max_ms INTEGER NULL,
    p95_ms INTEGER NULL,
    output_bytes INTEGER NULL,
    error_code TEXT NULL,
    error_message TEXT NULL
);

CREATE INDEX IF NOT EXISTS benchmark_runs_owner_started_idx
    ON benchmark_runs (owner_id, started_at DESC, benchmark_run_id ASC);

CREATE INDEX IF NOT EXISTS benchmark_runs_owner_status_idx
    ON benchmark_runs (owner_id, status);

CREATE INDEX IF NOT EXISTS benchmark_runs_template_idx
    ON benchmark_runs (template_id);

CREATE TABLE IF NOT EXISTS auth_sessions (
    session_id TEXT PRIMARY KEY,
    user_id TEXT NOT NULL REFERENCES users (user_id),
    issued_at TIMESTAMPTZ NOT NULL,
    expires_at TIMESTAMPTZ NOT NULL,
    is_revoked BOOLEAN NOT NULL,
    revoked_at TIMESTAMPTZ NULL
);

CREATE INDEX IF NOT EXISTS auth_sessions_user_active_idx
    ON auth_sessions (user_id, is_revoked, expires_at DESC);

CREATE TABLE IF NOT EXISTS password_reset_tokens (
    token_id TEXT PRIMARY KEY,
    user_id TEXT NOT NULL REFERENCES users (user_id),
    token_hash TEXT NOT NULL UNIQUE,
    issued_at TIMESTAMPTZ NOT NULL,
    expires_at TIMESTAMPTZ NOT NULL,
    used_at TIMESTAMPTZ NULL
);

CREATE INDEX IF NOT EXISTS password_reset_tokens_hash_idx
    ON password_reset_tokens (token_hash);
