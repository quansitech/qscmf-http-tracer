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

    public function start(string $method, string $url, array $requestHeaders, string $requestBody): string
    {
        $uniqueId = Str::uuid()->toString();
        $this->writer->writeRequest(
            $uniqueId,
            new \DateTimeImmutable('now'),
            $method,
            $url,
            $requestHeaders,
            $requestBody
        );
        return $uniqueId;
    }

    public function finish(string $uniqueId, int $responseStatusCode, array $responseHeaders, string $responseBody, float $durationMs): void
    {
        $this->writer->writeResponse($uniqueId, $responseStatusCode, $responseHeaders, $responseBody, $durationMs);
    }
}