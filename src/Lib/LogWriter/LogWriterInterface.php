<?php

namespace Qscmf\HttpTracer\Lib\LogWriter;

interface LogWriterInterface
{
    public function writeRequest(
        string $trace_id, 
        \DateTimeImmutable $start_time, 
        string $method, 
        string $url, 
        array $request_headers, 
        string $request_body
    ): void;

    public function writeResponse(
        string $trace_id, 
        int $response_status_code, 
        array $response_headers, 
        string $response_body, 
        float $duration_ms
    ): void;
}
