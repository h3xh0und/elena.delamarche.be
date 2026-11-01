<?php
/**
 * Elastic APM zonder extensie: de shared hosting laat geen PHP-agent toe, dus meldt dit bestand
 * per request één transactie (en een eventuele fatale fout) aan de intake API van de APM-server.
 * Bewust zonder URL-parameters, IP-adres, gebruikersnaam of foutboodschap.
 * Zonder includes/apm.local.php (geschreven door de deploy) doet dit niets.
 */
function apm_start(string $service): void
{
    $config = __DIR__ . '/apm.local.php';
    if (!is_file($config) || !function_exists('curl_init')) {
        return;
    }
    $apm = require $config;
    $start = microtime(true);

    register_shutdown_function(function () use ($apm, $service, $start) {
        $status = (int) (http_response_code() ?: 200);
        $duration = (microtime(true) - $start) * 1000;
        $fatal = error_get_last();
        if ($fatal && !($fatal['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR))) {
            $fatal = null;
        }
        // de bezoeker wacht niet op de melding
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }

        $traceId = bin2hex(random_bytes(16));
        $id = bin2hex(random_bytes(8));
        $timestamp = (int) ($start * 1000000);
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $failed = $fatal || $status >= 500;
        $events = [
            ['metadata' => ['service' => [
                'name' => $service,
                'environment' => 'production',
                'agent' => ['name' => 'php-lite', 'version' => '1.0'],
                'language' => ['name' => 'php', 'version' => PHP_VERSION],
            ]]],
            ['transaction' => [
                'id' => $id,
                'trace_id' => $traceId,
                'name' => $method . ' ' . ($_SERVER['SCRIPT_NAME'] ?? '/'),
                'type' => 'request',
                'duration' => $duration,
                'timestamp' => $timestamp,
                'result' => 'HTTP ' . intdiv($status, 100) . 'xx',
                'outcome' => $failed ? 'failure' : 'success',
                'sampled' => true,
                'span_count' => ['started' => 0],
                'context' => [
                    'request' => ['method' => $method, 'url' => ['pathname' => $_SERVER['SCRIPT_NAME'] ?? '/']],
                    'response' => ['status_code' => $status],
                ],
            ]],
        ];
        if ($fatal) {
            $events[] = ['error' => [
                'id' => bin2hex(random_bytes(16)),
                'trace_id' => $traceId,
                'transaction_id' => $id,
                'parent_id' => $id,
                'timestamp' => $timestamp,
                'culprit' => basename($fatal['file']) . ':' . $fatal['line'],
                'exception' => ['message' => 'PHP fatal error (type ' . $fatal['type'] . ')', 'type' => 'PHP fatal error'],
            ]];
        }

        $ch = curl_init(rtrim($apm['url'], '/') . '/intake/v2/events');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => implode("\n", array_map('json_encode', $events)) . "\n",
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-ndjson', 'Authorization: Bearer ' . $apm['token']],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT_MS => 1500,
        ]);
        curl_exec($ch);
        curl_close($ch);
    });
}
