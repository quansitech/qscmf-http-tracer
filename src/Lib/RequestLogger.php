<?php

namespace Qscmf\HttpTracer\Lib;

use Illuminate\Support\Str;
use Qscmf\HttpTracer\Lib\LogWriter\LogWriterInterface;

class RequestLogger
{
    private LogWriterInterface $writer;

    public function __construct(LogWriterInterface $writer)
    {
        $this->writer = $writer;
    }

    public function start(string $method, string $url, array $request_headers, string $request_body): string
    {
        $trace_id = Str::uuid()->toString();
        $this->writer->writeRequest(
            $trace_id,
            new \DateTimeImmutable('now'),
            $method,
            $url,
            $request_headers,
            $request_body
        );
        return $trace_id;
    }

    public function finish(string $trace_id, int $response_status_code, array $response_headers, string $response_body, float $duration_ms): void
    {
        $this->writer->writeResponse($trace_id, $response_status_code, $response_headers, $response_body, $duration_ms);
    }
}
