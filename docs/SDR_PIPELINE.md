# SDR Import Pipeline

The `sdr_import/` package is the **only** runtime MSSQL dependency in the C-DAT project. It restores `.bak` files into a temporary MSSQL instance, then migrates subscriber data into PostgreSQL.

## When to use

- Admin uploads SDR `.bak` files via `/data-upload/sdr`
- One-time migration of legacy SDR backups

## Requirements

- Docker (MSSQL container) or standalone MSSQL Server
- Python 3.10+
- Environment variables in `.env`:

| Variable | Purpose |
|----------|---------|
| `MSSQL_SA_PASSWORD` | SA password for restore container |
| `MSSQL_CONTAINER` | Docker container name (default `cdat-mssql`) |
| `CDR_DB_*` | Target PostgreSQL after migration |

## Operations

```bash
# Restore + migrate a .bak (see sdr_import/migrate.py for flags)
python3 -m sdr_import.migrate --help

# Long-running import service
python3 -m sdr_import.service
```

## Security

- MSSQL credentials must **never** appear in PHP web modules
- Run MSSQL on an isolated network segment
- Rotate `MSSQL_SA_PASSWORD` after each restore window
- Delete `.bak` staging files after successful import

## Monitoring

- Check Docker container health: `docker ps --filter name=cdat-mssql`
- Review Python logs under `logs/` or systemd journal for `sdr_import`

## Retirement

If SDR uploads are not offered in production, disable `/data-upload/sdr` in the menu and document that `sdr_import/` is archived but kept for disaster recovery.
