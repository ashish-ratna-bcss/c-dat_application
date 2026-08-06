from __future__ import annotations
from datetime import datetime
from typing import Any, Optional
from pydantic import BaseModel, Field

class HealthResponse(BaseModel):
    status: str = 'ok'
    service: str = 'document-processing-service'
    version: str = '0.2.0'
    modules: list[str] = Field(default_factory=lambda: ['cdr', 'sdr'])
    workers_max: int = 0
    workers_running: int = 0
    running_job_ids: list[int] = Field(default_factory=list)

class DocumentPreview(BaseModel):
    module: str
    operator: Optional[str] = None
    target_phone: Optional[str] = None
    mssql_database: Optional[str] = None
    total_records: int = 0
    warnings: list[str] = Field(default_factory=list)
    basename: str
    message: Optional[str] = None

class DocumentSubmitResponse(BaseModel):
    job_id: Optional[int] = None
    module: str
    status: str
    phase: Optional[str] = None
    message: str
    preview: DocumentPreview

class DocumentValidateResponse(BaseModel):
    status: str = 'validated'
    message: str
    preview: DocumentPreview

class JobStatusResponse(BaseModel):
    job_id: int
    module: str
    status: str
    phase: Optional[str] = None
    operator: Optional[str] = None
    target_phone: Optional[str] = None
    basename: Optional[str] = None
    mssql_database: Optional[str] = None
    total_records: Optional[int] = None
    rows_committed: int = 0
    last_checkpoint_key: int = 0
    progress_percent: Optional[float] = None
    phase_state: Optional[dict[str, Any]] = None
    error_message: Optional[str] = None
    dry_run: Optional[bool] = None
    message: str = ''
    created_at: Optional[datetime] = None
    updated_at: Optional[datetime] = None
    completed_at: Optional[datetime] = None

class JobListResponse(BaseModel):
    jobs: list[JobStatusResponse]
    count: int

class ResumableUploadInitRequest(BaseModel):
    filename: str
    file_size: int = Field(gt=0)
    file_key: str = Field(min_length=8)
    chunk_size: Optional[int] = Field(default=None, ge=1024 * 1024, le=64 * 1024 * 1024)

class ResumableUploadSessionResponse(BaseModel):
    upload_id: str
    module: str
    filename: str
    file_size: int
    chunk_size: int
    offset: int
    bytes_received: int
    progress_percent: float
    status: str
    resumed: bool
    complete: bool

class ResumableUploadCompleteResponse(BaseModel):
    upload_id: str
    job_id: Optional[int] = None
    module: str
    status: str
    message: str
    basename: str

class StagingRowsResponse(BaseModel):
    job_id: int
    batch_id: int
    module: str
    table_key: str
    table: str
    total: int
    duplicate_count: int
    valid_count: int
    rows: list[dict[str, Any]]

class StagingActionResponse(BaseModel):
    ok: bool = True
    job_id: Optional[int] = None
    batch_id: Optional[int] = None
    inserted: Optional[int] = None
    status: Optional[str] = None
    message: Optional[str] = None

ImportPreview = DocumentPreview
ImportSubmitResponse = DocumentSubmitResponse
ImportValidateResponse = DocumentValidateResponse

def job_to_response(data: dict[str, Any]) -> JobStatusResponse:
    total = data.get('total_records') or data.get('total_rows_estimated')
    committed = int(data.get('rows_committed') or 0)
    progress = None
    if total and total > 0:
        progress = round(min(100.0, committed / total * 100.0), 2)
    return JobStatusResponse(job_id=int(data['job_id']), module=data.get('module', 'cdr'), status=data.get('status', 'unknown'), phase=data.get('phase'), operator=data.get('operator'), target_phone=data.get('target_phone'), basename=data.get('basename') or data.get('source_basename'), mssql_database=data.get('mssql_database'), total_records=total, rows_committed=committed, last_checkpoint_key=int(data.get('last_checkpoint_key') or 0), progress_percent=progress, phase_state=data.get('phase_state'), error_message=data.get('error_message'), dry_run=data.get('dry_run'), message=data.get('message', ''), created_at=data.get('created_at'), updated_at=data.get('updated_at'), completed_at=data.get('completed_at'))
