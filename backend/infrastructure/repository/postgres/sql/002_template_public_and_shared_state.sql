ALTER TABLE templates
    ADD COLUMN IF NOT EXISTS is_public BOOLEAN NOT NULL DEFAULT FALSE;

CREATE INDEX IF NOT EXISTS templates_public_updated_idx
    ON templates (is_public, is_active, updated_at DESC, template_id ASC);

CREATE TABLE IF NOT EXISTS shared_states (
    state_id TEXT PRIMARY KEY,
    owner_id TEXT NULL REFERENCES users (user_id),
    payload_json JSONB NOT NULL,
    created_at TIMESTAMPTZ NOT NULL
);

CREATE INDEX IF NOT EXISTS shared_states_created_idx
    ON shared_states (created_at DESC, state_id ASC);
