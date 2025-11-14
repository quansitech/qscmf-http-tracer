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
        string $uniqueId, \DateTimeImmutable $startTime, string $method, 
        string $url, array $requestHeaders, string $requestBody
    ): void {
        $this->monolog->info('API Request Trace', [
            'stage' => 'start',
            'unique_id' => $uniqueId,
            'start_time' => $startTime,
            'method' => $method,
            'url' => $url,
            'request_headers' => $requestHeaders,
            'request_body' => $requestBody,
        ]);
    }

    public function writeResponse(
        string $uniqueId, int $responseStatusCode, array $responseHeaders, 
        string $responseBody, float $durationMs
    ): void {
        $this->monolog->info('API Request Trace', [
            'stage' => 'end',
            'unique_id' => $uniqueId,
            'response_status_code' => $responseStatusCode,
            'response_headers' => $responseHeaders,
            'response_body' => $responseBody,
            'duration_ms' => $durationMs,
        ]);
    }
    
}