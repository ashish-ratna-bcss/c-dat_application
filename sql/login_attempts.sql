-- Login rate-limit table (also created on first login if missing).
CREATE TABLE IF NOT EXISTS login_attempts (
    id              SERIAL       PRIMARY KEY,
    username        VARCHAR(100) NOT NULL,
    attempted_at    TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    success         BOOLEAN      NOT NULL DEFAULT FALSE
);

CREATE INDEX IF NOT EXISTS idx_login_attempts_user_time ON login_attempts (username, attempted_at DESC);
