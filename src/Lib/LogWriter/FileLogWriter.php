<?php

namespace Qscmf\HttpTracer\Lib\LogWriter;

class FileLogWriter implements LogWriterInterface
{
    private string $logFilePath;

    public function __construct(string $logFilePath = '')
    {
        $logFilePath = $logFilePath ?: __DIR__ . '/../../logs/'.date('Y_m_d').'_http_trace.log';
        $directory = dirname($logFilePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }
        $this->logFilePath = $logFilePath;
    }

    public function writeRequest(string $uniqueId, \DateTimeImmutable $startTime, string $method, string $url, array $requestHeaders, string $requestBody): void
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

        $r = file_put_contents($this->logFilePath, $logEntry, FILE_APPEND);

        if ($r === false) {
            $error = error_get_last();
            error_log("文件写入失败: " . ($error['message'] ?? '未知错误'));
        }
    }

    public function writeResponse(string $uniqueId, int $responseStatusCode, array $responseHeaders, string $responseBody, float $durationMs): void
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

        $r = file_put_contents($this->logFilePath, $logEntry, FILE_APPEND);

        if ($r === false) {
            $error = error_get_last();
            error_log("文件写入失败: " . ($error['message'] ?? '未知错误'));
        }
    }
    
    private function truncate(string $string, int $length = 1000): string
    {
        return strlen($string) > $length ? substr($string, 0, $length) . '... (truncated)' : $string;
    }
}