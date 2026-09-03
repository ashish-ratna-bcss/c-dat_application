#!/usr/bin/env python3
"""CDAT Data Upload FastAPI service.

Run from this folder:
    python main.py

Or:
    uvicorn main:app --host 127.0.0.1 --port 8090
"""
from __future__ import annotations

import sys
from contextlib import asynccontextmanager
from pathlib import Path
from typing import Optional

from fastapi import Depends, FastAPI, File, Form, Header, HTTPException, UploadFile
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import JSONResponse
from pydantic import BaseModel

SERVICE_ROOT = Path(__file__).resolve().parent
if str(SERVICE_ROOT) not in sys.path:
    sys.path.insert(0, str(SERVICE_ROOT))

from config import settings, unique_stored_path
from cdr.preview import preview_csv_bytes
from cdr.staging import drop_unused_template_table
from cdr.dataProcessing import (
    create_stage_job,
    enqueue_insert,
    job_rows,
    list_jobs,
    start_pipeline,
)
from db import ensure_databases_and_schema, ping

settings.configure_logging()


class HealthResponse(BaseModel):
    status: str = "ok"
    service: str = "dataUpload"
    version: str = settings.api_version
    db: Optional[dict] = None


class UploadResponse(BaseModel):
    upload_id: str
    filename: str
    stored_as: str
    size_bytes: int
    content_type: Optional[str] = None
    message: str = "File stored successfully."


class ErrorResponse(BaseModel):
    detail: str


def verify_api_key(
    x_api_key: Optional[str] = Header(default=None, alias="X-API-Key"),
) -> None:
    if settings.api_key and x_api_key != settings.api_key:
        raise HTTPException(status_code=401, detail="Invalid or missing X-API-Key header")


auth_deps = [Depends(verify_api_key)] if settings.api_key else []


@asynccontextmanager
async def lifespan(_app: FastAPI):
    settings.configure_logging()
    print(f"Upload dirs: {settings.upload_dir} and {settings.cdr_upload_dir}")
    print(f"Log file: {settings.log_file}")
    summary = ensure_databases_and_schema()
    ping()
    created = summary.get("created_databases") or []
    if created:
        print("Created databases:", ", ".join(created))
    schema_info = summary.get("pcsuspect_schema") or {}
    if schema_info:
        print(
            f"Schema {schema_info.get('schema')} in {schema_info.get('database')}: "
            f"{'ready' if schema_info.get('ok') else 'missing'}"
        )
    upload_info = summary.get("upload_schema") or {}
    if upload_info:
        print(
            f"Schema {upload_info.get('schema')} in {upload_info.get('database')}: "
            f"{'ready' if upload_info.get('ok') else 'missing'}"
        )
    try:
        drop_unused_template_table()
    except Exception as exc:
        print(f"Could not drop unused staging template table: {exc}")
    start_pipeline()
    print("CDR pipeline: 2 parallel background jobs")
    yield


def create_app() -> FastAPI:
    app = FastAPI(
        title=settings.api_title,
        description="FastAPI service for CDAT data uploads.",
        version=settings.api_version,
        lifespan=lifespan,
    )
    wildcard_cors = settings.cors_origins == ["*"]
    app.add_middleware(
        CORSMiddleware,
        allow_origins=settings.cors_origins,
        allow_credentials=not wildcard_cors,
        allow_methods=["*"],
        allow_headers=["*"],
    )

    @app.get("/health", response_model=HealthResponse, tags=["system"])
    @app.get(f"{settings.api_prefix}/health", response_model=HealthResponse, tags=["system"])
    def health() -> HealthResponse:
        try:
            return HealthResponse(db=ping())
        except Exception as exc:
            raise HTTPException(
                status_code=503,
                detail=f"Database unavailable: {exc}",
            ) from exc

    @app.get(f"{settings.api_prefix}/config", tags=["system"], dependencies=auth_deps)
    def runtime_config() -> JSONResponse:
        return JSONResponse(
            {
                "upload_dir": str(settings.upload_dir.resolve()),
                "cdr_upload_dir": str(settings.cdr_upload_dir.resolve()),
                "max_upload_mb": settings.max_upload_mb,
                "db": {
                    "host": settings.db_host,
                    "port": settings.db_port,
                    "name": settings.db_name,
                    "user": settings.db_user,
                    "pcsuspect_schema": settings.pcsuspect_schema,
                },
            }
        )

    @app.post(
        f"{settings.api_prefix}/uploads",
        response_model=UploadResponse,
        responses={400: {"model": ErrorResponse}, 413: {"model": ErrorResponse}},
        tags=["uploads"],
        dependencies=auth_deps,
    )
    async def upload_file(file: UploadFile = File(...)) -> UploadResponse:
        original_name = Path(file.filename or "upload").name
        if not original_name or original_name in {".", ".."}:
            raise HTTPException(status_code=400, detail="A valid filename is required.")

        settings.ensure_runtime_dirs()
        dest = unique_stored_path(settings.upload_dir, original_name)
        max_bytes = settings.max_upload_mb * 1024 * 1024
        size = 0
        chunk_size = 8 * 1024 * 1024

        try:
            with dest.open("wb") as out:
                while True:
                    chunk = await file.read(chunk_size)
                    if not chunk:
                        break
                    size += len(chunk)
                    if size > max_bytes:
                        dest.unlink(missing_ok=True)
                        raise HTTPException(
                            status_code=413,
                            detail=f"File exceeds the {settings.max_upload_mb} MB limit.",
                        )
                    out.write(chunk)
        finally:
            await file.close()

        if size == 0:
            dest.unlink(missing_ok=True)
            raise HTTPException(status_code=400, detail="Uploaded file is empty.")

        return UploadResponse(
            upload_id=dest.stem,
            filename=original_name,
            stored_as=dest.name,
            size_bytes=size,
            content_type=file.content_type,
        )

    @app.post(
        f"{settings.api_prefix}/cdr/preview",
        responses={400: {"model": ErrorResponse}, 413: {"model": ErrorResponse}},
        tags=["cdr"],
        dependencies=auth_deps,
    )
    async def cdr_preview(file: UploadFile = File(...)) -> JSONResponse:
        original_name = Path(file.filename or "upload.csv").name
        if not original_name or original_name in {".", ".."}:
            raise HTTPException(status_code=400, detail="A valid filename is required.")
        if Path(original_name).suffix.lower() != ".csv":
            raise HTTPException(status_code=400, detail="Only .csv files are accepted.")

        max_bytes = settings.max_upload_mb * 1024 * 1024
        chunks: list[bytes] = []
        size = 0
        chunk_size = 8 * 1024 * 1024

        try:
            while True:
                chunk = await file.read(chunk_size)
                if not chunk:
                    break
                size += len(chunk)
                if size > max_bytes:
                    raise HTTPException(
                        status_code=413,
                        detail=f"File exceeds the {settings.max_upload_mb} MB limit.",
                    )
                chunks.append(chunk)
        finally:
            await file.close()

        if size == 0:
            raise HTTPException(status_code=400, detail="Uploaded file is empty.")

        try:
            result = preview_csv_bytes(b"".join(chunks), filename=original_name)
        except ValueError as exc:
            raise HTTPException(status_code=400, detail=str(exc)) from exc

        result["size_bytes"] = size
        result["original_filename"] = original_name
        return JSONResponse(result)

    @app.post(
        f"{settings.api_prefix}/cdr/stage",
        responses={400: {"model": ErrorResponse}, 413: {"model": ErrorResponse}},
        tags=["cdr"],
        dependencies=auth_deps,
    )
    async def cdr_stage(
        file: UploadFile = File(...),
        username: str = Form("user"),
        ip_address: str = Form(""),
    ) -> JSONResponse:
        original_name = Path(file.filename or "upload.csv").name
        if not original_name or original_name in {".", ".."}:
            raise HTTPException(status_code=400, detail="A valid filename is required.")
        if Path(original_name).suffix.lower() != ".csv":
            raise HTTPException(status_code=400, detail="Only .csv files are accepted.")

        settings.ensure_runtime_dirs()
        max_bytes = settings.max_upload_mb * 1024 * 1024
        user = (username or "user").strip() or "user"
        dest = unique_stored_path(settings.cdr_upload_dir, original_name, username=user)
        size = 0
        chunk_size = 8 * 1024 * 1024
        try:
            with dest.open("wb") as out:
                while True:
                    chunk = await file.read(chunk_size)
                    if not chunk:
                        break
                    size += len(chunk)
                    if size > max_bytes:
                        dest.unlink(missing_ok=True)
                        raise HTTPException(
                            status_code=413,
                            detail=f"File exceeds the {settings.max_upload_mb} MB limit.",
                        )
                    out.write(chunk)
        finally:
            await file.close()

        if size == 0:
            dest.unlink(missing_ok=True)
            raise HTTPException(status_code=400, detail="Uploaded file is empty.")

        try:
            result = create_stage_job(
                filename=original_name,
                file_path=str(dest),
                file_size=size,
                username=user,
                ip_address=(ip_address or "").strip(),
            )
        except Exception as exc:
            dest.unlink(missing_ok=True)
            raise HTTPException(
                status_code=500,
                detail=f"Could not queue staging job: {exc}",
            ) from exc

        result["size_bytes"] = size
        result["queued"] = True
        return JSONResponse(result)

    @app.get(f"{settings.api_prefix}/cdr/jobs", tags=["cdr"], dependencies=auth_deps)
    def cdr_jobs(ids: Optional[str] = None) -> JSONResponse:
        job_ids: Optional[list[int]] = None
        if ids:
            job_ids = [int(part) for part in ids.split(",") if part.strip().isdigit()]
        return JSONResponse({"ok": True, "jobs": list_jobs(job_ids)})

    @app.post(
        f"{settings.api_prefix}/cdr/jobs/{{job_id}}/insert",
        tags=["cdr"],
        dependencies=auth_deps,
    )
    def cdr_job_insert(job_id: int) -> JSONResponse:
        try:
            return JSONResponse(enqueue_insert(job_id))
        except ValueError as exc:
            raise HTTPException(status_code=400, detail=str(exc)) from exc

    @app.get(
        f"{settings.api_prefix}/cdr/jobs/{{job_id}}/rows",
        tags=["cdr"],
        dependencies=auth_deps,
    )
    def cdr_job_rows(job_id: int, limit: int = 200, offset: int = 0) -> JSONResponse:
        try:
            return JSONResponse(job_rows(job_id, limit=min(limit, 500), offset=max(offset, 0)))
        except ValueError as exc:
            raise HTTPException(status_code=400, detail=str(exc)) from exc

    return app


app = create_app()


def run() -> None:
    import uvicorn

    uvicorn.run(
        "main:app",
        host=settings.host,
        port=settings.port,
        reload=False,
        app_dir=str(SERVICE_ROOT),
        log_config=None,
    )


if __name__ == "__main__":
    run()
