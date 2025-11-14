<?php

namespace Qscmf\HttpTracer\Lib;

use Monolog\Formatter\FormatterInterface;

class FileFormatter implements FormatterInterface
{
    public function format(array $record): string
    {
        if (!isset($record['context']['stage'])) {
            return '';
        }

        $context = $record['context'];
        $log_entry = '';

        switch ($context['stage']) {
            case 'start':
                $log_entry = $this->formatRequest(
                    $context['unique_id'],
                    $context['start_time'],
                    $context['method'],
                    $context['url'],
                    $context['request_headers'],
                    $context['request_body']
                );
                break;

            case 'end':
                $log_entry = $this->formatResponse(
                    $context['unique_id'],
                    $context['response_status_code'],
                    $context['response_headers'],
                    $context['response_body'],
                    $context['duration_ms']
                );
                break;
        }

        return $log_entry;
    }

    public function formatBatch(array $records): string
    {
        $message = '';
        foreach ($records as $record) {
            $message .= $this->format($record);
        }
        return $message;
    }

    public function formatRequest(string $trace_id, \DateTimeImmutable $start_time, string $method, string $url, array $request_headers, string $request_body): string
    {
        $log_entry = sprintf(
            "[%s] [%s] [START] %s %s\n--> Request Headers: %s\n--> Request Body: %s\n---\n",
            $start_time->format('Y-m-d H:i:s.u'),
            $trace_id,
            $method,
            $url,
            json_encode($request_headers, JSON_UNESCAPED_UNICODE),
            $request_body
        );

        return $log_entry;
    }

    public function formatResponse(string $trace_id, int $response_status_code, array $response_headers, string $response_body, float $duration_ms): string
    {
        $log_entry = sprintf(
            "[%s] [%s] [END] Status: %d, Duration: %.2f ms\n--> Response Headers: %s\n--> Response Body: %s\n%s\n\n",
            (new \DateTimeImmutable('now'))->format('Y-m-d H:i:s.u'),
            $trace_id,
            $response_status_code,
            $duration_ms,
            json_encode($response_headers, JSON_UNESCAPED_UNICODE),
            $this->truncate($response_body),
            str_repeat('=', 80)
        );

        return $log_entry;
    }
    
    private function truncate(string $string, int $length = 1000): string
    {
        return strlen($string) > $length ? substr($string, 0, $length) . '... (truncated)' : $string;
    }
}
