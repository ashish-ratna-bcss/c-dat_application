-- Per-user dashboard quick links.
--
-- One row per pinned page per user. Only the URL is authoritative: the label
-- and icon are resolved from controller/includes/menu.php when the tiles are
-- drawn, so renaming a menu entry renames the tile, and a page removed from
-- the menu quietly drops off every dashboard instead of leaving a dead tile.
--
-- The application creates this table on first use as well; this file is here
-- so a DBA can create it up front, and so the Linux deploy does not depend on
-- the web user holding DDL rights.

CREATE TABLE IF NOT EXISTS user_quick_links (
    id          SERIAL PRIMARY KEY,
    username    VARCHAR(100) NOT NULL,
    url         VARCHAR(255) NOT NULL,
    label       VARCHAR(150) NOT NULL DEFAULT '',
    position    INTEGER      NOT NULL DEFAULT 0,
    created_at  TIMESTAMP    NOT NULL DEFAULT NOW(),
    CONSTRAINT user_quick_links_unique UNIQUE (username, url)
);

CREATE INDEX IF NOT EXISTS user_quick_links_user_pos
    ON user_quick_links (username, position);
