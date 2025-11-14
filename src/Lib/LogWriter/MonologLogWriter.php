<?php

namespace Qscmf\HttpTracer\Lib\LogWriter;

use Monolog\Logger;

class MonologLogWriter implements LogWriterInterface
{
    private Logger $monolog;

    public function __construct(Logger $monolog)
    {
        $this->monolog = $monolog;
    }

    public function writeRequest(
        string $trace_id, \DateTimeImmutable $start_time, string $method, 
        string $url, array $request_headers, string $request_body
    ): void {
        $this->monolog->info('API Request Trace', [
            'stage' => 'start',
            'unique_id' => $trace_id,
            'start_time' => $start_time,
            'method' => $method,
            'url' => $url,
            'request_headers' => $request_headers,
            'request_body' => $request_body,
        ]);
    }

    public function writeResponse(
        string $trace_id, int $response_status_code, array $response_headers, 
        string $response_body, float $duration_ms
    ): void {
        $this->monolog->info('API Request Trace', [
            'stage' => 'end',
            'unique_id' => $trace_id,
            'response_status_code' => $response_status_code,
            'response_headers' => $response_headers,
            'response_body' => $response_body,
            'duration_ms' => $duration_ms,
        ]);
    }
    
}
