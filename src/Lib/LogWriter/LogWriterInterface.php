<?php

namespace Qscmf\HttpTracer\Lib\LogWriter;

interface LogWriterInterface
{
    public function writeRequest(
        string $uniqueId, 
        \DateTimeImmutable $startTime, 
        string $method, 
        string $url, 
        array $requestHeaders, 
        string $requestBody
    ): void;

    public function writeResponse(
        string $uniqueId, 
        int $responseStatusCode, 
        array $responseHeaders, 
        string $responseBody, 
        float $durationMs
    ): void;
}