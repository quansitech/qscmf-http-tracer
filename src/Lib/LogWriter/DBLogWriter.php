<?php

namespace Qscmf\HttpTracer\Lib\LogWriter;

use Qscmf\HttpTracer\Lib\Helper;

class DBLogWriter implements LogWriterInterface
{

    private string $table_name;

    public function __construct()
    {
        $this->table_name = Helper::getTableName();
    }

    public function writeRequest(string $uniqueId, \DateTimeImmutable $startTime, string $method, string $url, array $requestHeaders, string $requestBody): void
    {
        try {
            $data = [
                'trace_id' => ':trace_id',
                'method' => ':method',
                'url' => ':url',
                'request_headers' => ':request_headers',
                'request_body' => ':request_body',
                'create_date' => ':create_date',
            ];

            $bind = [
                ':trace_id' => $uniqueId,
                ':method' => $method,
                ':url' => $url,
                ':request_headers' => json_encode($requestHeaders),
                ':request_body' => $requestBody,
                ':create_date' => $startTime->format('Y-m-d H:i:s.u'),
            ];

            $r = D()->table($this->table_name)->bind($bind)->add($data);

        } catch (\PDOException $e) {
            error_log("Failed to write initial request log: " . $e->getMessage());
        }
    }

    public function writeResponse(string $uniqueId, int $responseStatusCode, array $responseHeaders, string $responseBody, float $durationMs): void
    {
        try {

            $data = [
                'response_status_code' => ':response_status_code',
                'response_headers' => ':response_headers',
                'response_body' => ':response_body',
                'duration_ms' => ':duration_ms',
                'trace_id' => ':trace_id',
            ];

            $bind = [
                ':response_status_code' => $responseStatusCode,
                ':response_headers' => json_encode($responseHeaders),
                ':response_body' => $responseBody,
                ':duration_ms' => $durationMs,
                ':trace_id' => $uniqueId,
            ];

            $r = D()->table($this->table_name)->bind($bind)->where(['trace_id' => ':trace_id'])->save($data);

        } catch (\PDOException $e) {
            error_log("Failed to update response log: " . $e->getMessage());
        }
    }
}