<?php

namespace Qscmf\HttpTracer\Lib\LogWriter;

use Qscmf\HttpTracer\Lib\Helper;
use Monolog\Handler\AbstractProcessingHandler;

class DBHandler extends AbstractProcessingHandler implements LogWriterInterface
{

    private string $table_name;

    public function __construct()
    {
        $this->table_name = Helper::getTableName();
    }

    protected function write(array $record): void
    {
        if (!isset($record['context']['stage'])) {
            return;
        }

        $context = $record['context'];

        try {
            switch ($context['stage']) {
                case 'start':
                    $this->writeRequest(
                        $context['trace_id'],
                        $context['start_time'],
                        $context['method'],
                        $context['url'],
                        $context['request_headers'],
                        $context['request_body']
                    );
                    break;

                case 'end':
                    $this->writeResponse(
                        $context['trace_id'],
                        $context['response_status_code'],
                        $context['response_headers'],
                        $context['response_body'],
                        $context['duration_ms']
                    );
                    break;
            }
        } catch (\PDOException $e) {
            $this->logError($e, $context['stage'] ?? 'unknown', $context['trace_id'] ?? 'unknown', 'database_error');
        } catch (\Exception $e) {
            $this->logError($e, $context['stage'] ?? 'unknown', $context['trace_id'] ?? 'unknown', 'general_error');
        }
    }

    public function writeRequest(string $trace_id, \DateTimeImmutable $start_time, string $method, string $url, array $request_headers, string $request_body): void
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
                ':trace_id' => $trace_id,
                ':method' => $method,
                ':url' => $url,
                ':request_headers' => json_encode($request_headers),
                ':request_body' => $request_body,
                ':create_date' => $start_time->format('Y-m-d H:i:s.u'),
            ];

            $r = D()->table($this->table_name)->bind($bind)->add($data);

        } catch (\PDOException $e) {
            $this->logError($e, 'start', $trace_id, 'write_request_failed');
        } catch (\Exception $e) {
            $this->logError($e, 'start', $trace_id, 'write_request_exception');
        }
    }

    public function writeResponse(string $trace_id, int $response_status_code, array $response_headers, string $response_body, float $duration_ms): void
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
                ':response_status_code' => $response_status_code,
                ':response_headers' => json_encode($response_headers),
                ':response_body' => $response_body,
                ':duration_ms' => $duration_ms,
                ':trace_id' => $trace_id,
            ];

            $r = D()->table($this->table_name)->bind($bind)->where(['trace_id' => ':trace_id'])->save($data);

        } catch (\PDOException $e) {
            $this->logError($e, 'end', $trace_id, 'write_response_failed');
        } catch (\Exception $e) {
            $this->logError($e, 'end', $trace_id, 'write_response_exception');
        }
    }

    private function logError(\Exception $e, string $stage, string $trace_id, string $context = ''): void
    {
        $message = sprintf(
            "QSCMF HTTP Tracer Error [stage:%s, trace_id:%s, context:%s]: %s in %s:%d",
            $stage,
            $trace_id,
            $context ?: 'general',
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        );
        
        error_log($message);
    }
}
