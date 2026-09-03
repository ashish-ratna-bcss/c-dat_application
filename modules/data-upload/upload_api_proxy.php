<?php
/**
 * Authenticated PHP proxy to the local FastAPI data-upload service.
 *
 * Browser traffic stays on the PHP session boundary. The FastAPI key never
 * reaches the client. Requires poweruser/admin (uploader role).
 */
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/activity_logger.php';
require_once CDAT_COMMON . '/csrf.php';

audit_require_uploader();

function cdat_upload_env(string $key, string $default = ''): string
{
    cdat_load_dotenv();
    $v = getenv($key);
    if ($v === false || $v === '') {
        return $default;
    }
    return (string) $v;
}

function cdat_data_upload_base_url(): string
{
    $url = cdat_upload_env('DATA_UPLOAD_URL');
    if ($url === '/' || strcasecmp($url, 'same') === 0) {
        return '';
    }
    if ($url !== '') {
        return rtrim($url, '/');
    }
    $host = cdat_upload_env('DATA_UPLOAD_HOST', '127.0.0.1');
    $port = cdat_upload_env('DATA_UPLOAD_PORT', '8090');
    // Prefer loopback for server-side proxy even if the public URL differs.
    if ($host === '0.0.0.0' || $host === '::' || $host === '[::]') {
        $host = '127.0.0.1';
    }
    return 'http://' . $host . ':' . $port;
}

function cdat_data_upload_api_key(): string
{
    $key = cdat_upload_env('DATA_UPLOAD_API_KEY');
    if ($key === '') {
        $key = cdat_upload_env('CDR_API_KEY');
    }
    return $key;
}

/**
 * Map /api/data-upload/... onto FastAPI /api/v1/...
 */
function cdat_upload_proxy_target(): string
{
    $uri = (string) ($_SERVER['REQUEST_URI'] ?? '');
    $path = parse_url($uri, PHP_URL_PATH) ?: '';
    $marker = '/api/data-upload';
    $pos = strpos($path, $marker);
    if ($pos === false) {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'detail' => 'Not found']);
        exit;
    }
    $suffix = substr($path, $pos + strlen($marker));
    $suffix = '/' . ltrim($suffix, '/');
    if ($suffix === '/') {
        $suffix = '';
    }
    $query = parse_url($uri, PHP_URL_QUERY);
    $target = cdat_data_upload_base_url() . '/api/v1' . $suffix;
    if ($query) {
        $target .= '?' . $query;
    }
    return $target;
}

function cdat_upload_proxy_forward(string $target): void
{
    if (!function_exists('curl_init')) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'detail' => 'Upload proxy requires PHP curl.']);
        exit;
    }

    $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    if ($method === 'POST' || $method === 'PUT' || $method === 'PATCH' || $method === 'DELETE') {
        csrf_verify();
    }

    $ch = curl_init($target);
    $headers = ['Accept: application/json'];
    $apiKey = cdat_data_upload_api_key();
    if ($apiKey !== '') {
        $headers[] = 'X-API-Key: ' . $apiKey;
    }

    $opts = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_TIMEOUT        => 600,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => false,
    ];

    if ($method === 'POST' || $method === 'PUT' || $method === 'PATCH') {
        $contentType = (string) ($_SERVER['CONTENT_TYPE'] ?? '');
        if (str_contains($contentType, 'multipart/form-data')) {
            $post = [];
            foreach ($_POST as $k => $v) {
                if ($k === 'csrf_token') {
                    continue;
                }
                $post[$k] = $v;
            }
            // Force username/ip from the authenticated session — never trust client.
            $post['username'] = (string) ($_SESSION['audit_username'] ?? 'user');
            $post['ip_address'] = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
            foreach ($_FILES as $field => $info) {
                if (!is_array($info) || ($info['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                    continue;
                }
                $post[$field] = new CURLFile(
                    $info['tmp_name'],
                    $info['type'] ?: 'application/octet-stream',
                    $info['name'] ?: 'upload.bin'
                );
            }
            $opts[CURLOPT_POSTFIELDS] = $post;
        } else {
            $raw = file_get_contents('php://input');
            $opts[CURLOPT_POSTFIELDS] = $raw === false ? '' : $raw;
            if ($contentType !== '') {
                $headers[] = 'Content-Type: ' . $contentType;
                $opts[CURLOPT_HTTPHEADER] = $headers;
            }
        }
    }

    curl_setopt_array($ch, $opts);
    $body = curl_exec($ch);
    $errno = curl_errno($ch);
    $err = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $respType = (string) curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

    if ($errno) {
        error_log('upload proxy curl error: ' . $err);
        http_response_code(502);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'detail' => 'Upload service unavailable.']);
        exit;
    }

    http_response_code($status > 0 ? $status : 502);
    if ($respType !== '') {
        header('Content-Type: ' . $respType);
    } else {
        header('Content-Type: application/json; charset=utf-8');
    }
    header('Cache-Control: no-store');
    echo $body === false ? '' : $body;
}

$target = cdat_upload_proxy_target();
cdat_upload_proxy_forward($target);
