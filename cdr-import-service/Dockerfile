FROM python:3.12-slim

WORKDIR /app

COPY cdr_import /app/cdr_import
COPY sdr_import /app/sdr_import
COPY document_processing /app/document_processing
COPY sql /app/sql

COPY cdr-import-service/requirements.txt /app/cdr-import-service/requirements.txt
RUN pip install --no-cache-dir -r /app/cdr-import-service/requirements.txt

COPY cdr-import-service /app/cdr-import-service

ENV PYTHONPATH=/app
ENV CDR_API_HOST=0.0.0.0
ENV CDR_API_PORT=8088
ENV CDR_API_UPLOAD_DIR=/data/inbox

RUN mkdir -p /data/inbox /data/processing /data/done /data/failed

WORKDIR /app/cdr-import-service

EXPOSE 8088

CMD ["python3", "-m", "uvicorn", "app.main:app", "--host", "0.0.0.0", "--port", "8088"]
