"""dataUpload settings. Database values come from the repo-root .env."""
from __future__ import annotations

import os
import re
from datetime import datetime
from pathlib import Path

SERVICE_ROOT = Path(__file__).resolve().parent
REPO_ROOT = SERVICE_ROOT.parent
ROOT_ENV = REPO_ROOT / ".env"

_DB_ENV = {
    "host": "CDR_DB_HOST",
    "port": "CDR_DB_PORT",
    "database": "CDR_DB_NAME",
    "user": "CDR_DB_USER",
    "password": "CDR_DB_PASSWORD",
}
_DB_DEFAULTS = {
    "host": "127.0.0.1",
    "port": "5432",
    "database": "CDATDUPL_DB",
    "user": "postgres",
}


def _load_root_env() -> None:
    """Load KEY=VALUE pairs from the application root .env into os.environ."""
    if not ROOT_ENV.is_file():
        return
    for raw in ROOT_ENV.read_text(encoding="utf-8", errors="ignore").splitlines():
        line = raw.strip()
        if not line or line.startswith("#") or "=" not in line:
            continue
        key, value = line.split("=", 1)
        os.environ.setdefault(key.strip(), value.strip().strip('"').strip("'"))


def _env(name: str, default: str = "") -> str:
    return os.environ.get(name, default)


def load_db_config() -> dict[str, str]:
    """PostgreSQL settings from the root .env (and process environment)."""
    cfg: dict[str, str] = {}
    for key, env_name in _DB_ENV.items():
        if env_name in os.environ:
            cfg[key] = os.environ.get(env_name, "")
    for key, fallback in _DB_DEFAULTS.items():
        cfg.setdefault(key, fallback)
    cfg.setdefault("password", "")
    return cfg


def unique_stored_path(dest_dir: Path, filename: str, *, username: str = "") -> Path:
    """Save as {user}_{original}_{YYYYMMDD_HHMMSS}.ext so the disk name is readable."""
    dest_dir.mkdir(parents=True, exist_ok=True)
    stamp = datetime.now().strftime("%Y%m%d_%H%M%S")
    stem = re.sub(r"[^A-Za-z0-9._-]+", "_", Path(filename).stem).strip("._") or "upload"
    suffix = Path(filename).suffix.lower() or ".csv"
    user = re.sub(r"[^A-Za-z0-9]+", "_", username or "").strip("_").lower()
    name = f"{user}_{stem}_{stamp}{suffix}" if user else f"{stem}_{stamp}{suffix}"
    dest = dest_dir / name
    if not dest.exists():
        return dest
    for index in range(2, 100):
        extra = f"{user}_{stem}_{stamp}_{index}{suffix}" if user else f"{stem}_{stamp}_{index}{suffix}"
        dest = dest_dir / extra
        if not dest.exists():
            return dest
    raise ValueError("Could not allocate a unique stored filename.")


_load_root_env()


class Settings:
    api_title: str = "CDAT Data Upload API"
    api_version: str = "0.1.0"
    api_prefix: str = "/api/v1"
    host: str = _env("DATA_UPLOAD_HOST", "127.0.0.1")
    port: int = int(_env("DATA_UPLOAD_PORT", "8090"))
    api_key: str = _env("DATA_UPLOAD_API_KEY") or _env("CDR_API_KEY")
    cors_origins: list[str] = [
        origin.strip()
        for origin in _env("DATA_UPLOAD_CORS_ORIGINS", "*").split(",")
        if origin.strip()
    ]
    upload_dir: Path = Path(_env("DATA_UPLOAD_DIR", str(SERVICE_ROOT / "uploads")))
    max_upload_mb: int = int(_env("DATA_UPLOAD_MAX_MB", "512"))
    pcsuspect_schema: str = _env("CDAT_PCSUSPECT_SCHEMA", "cdatpcsuspectstagingdb")
    upload_schema: str = _env("CDAT_UPLOAD_SCHEMA", "cdatupload")

    db_host: str
    db_port: str
    db_name: str
    db_user: str
    db_password: str

    ir_db_name: str = _env("IR_DB_NAME", "IR_DB")
    jrms_db_name: str = _env("JRMS_DB_NAME", "JRMS_DB")
    pdact_db_name: str = _env("PDACT_DB_NAME", "PDACT_DB")
    rowdy_sheets_db_name: str = _env("ROWDY_SHEETS_DB_NAME", "ROWDY_SHEETS_DB")
    training_db_name: str = _env("TRAINING_DB_NAME", "TRAINING_DB")

    def __init__(self) -> None:
        db = load_db_config()
        self.db_host = db["host"]
        self.db_port = db["port"]
        self.db_name = db["database"]
        self.db_user = db["user"]
        self.db_password = db["password"]
        if not self.upload_dir.is_absolute():
            self.upload_dir = (SERVICE_ROOT / self.upload_dir).resolve()
        self.cdr_upload_dir = self.upload_dir / "cdr"

    def ensure_runtime_dirs(self) -> None:
        """Create uploads/ and uploads/cdr/ when the server starts."""
        self.upload_dir.mkdir(parents=True, exist_ok=True)
        self.cdr_upload_dir.mkdir(parents=True, exist_ok=True)


settings = Settings()
