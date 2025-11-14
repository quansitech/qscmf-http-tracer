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
        $logEntry = '';

        switch ($context['stage']) {
            case 'start':
                $logEntry = $this->formatRequest(
                    $context['unique_id'],
                    $context['start_time'],
                    $context['method'],
                    $context['url'],
                    $context['request_headers'],
                    $context['request_body']
                );
                break;

            case 'end':
                $logEntry = $this->formatResponse(
                    $context['unique_id'],
                    $context['response_status_code'],
                    $context['response_headers'],
                    $context['response_body'],
                    $context['duration_ms']
                );
                break;
        }

        return $logEntry;
    }

    public function formatBatch(array $records): string
    {
        $message = '';
        foreach ($records as $record) {
            $message .= $this->format($record);
        }
        return $message;
    }

    public function formatRequest(string $uniqueId, \DateTimeImmutable $startTime, string $method, string $url, array $requestHeaders, string $requestBody): string
    {
        $logEntry = sprintf(
            "[%s] [%s] [START] %s %s\n--> Request Headers: %s\n--> Request Body: %s\n---\n",
            $startTime->format('Y-m-d H:i:s.u'),
            $uniqueId,
            $method,
            $url,
            json_encode($requestHeaders, JSON_UNESCAPED_UNICODE),
            $requestBody
        );

        return $logEntry;
    }

    public function formatResponse(string $uniqueId, int $responseStatusCode, array $responseHeaders, string $responseBody, float $durationMs): string
    {
        $logEntry = sprintf(
            "[%s] [%s] [END] Status: %d, Duration: %.2f ms\n--> Response Headers: %s\n--> Response Body: %s\n%s\n\n",
            (new \DateTimeImmutable('now'))->format('Y-m-d H:i:s.u'),
            $uniqueId,
            $responseStatusCode,
            $durationMs,
            json_encode($responseHeaders, JSON_UNESCAPED_UNICODE),
            $this->truncate($responseBody),
            str_repeat('=', 80)
        );

        return $logEntry;
    }
    
    private function truncate(string $string, int $length = 1000): string
    {
        return strlen($string) > $length ? substr($string, 0, $length) . '... (truncated)' : $string;
    }
}