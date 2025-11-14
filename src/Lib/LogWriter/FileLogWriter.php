<?php

namespace Qscmf\HttpTracer\Lib\LogWriter;

use Qscmf\HttpTracer\Lib\FileFormatter;
use Qscmf\HttpTracer\Lib\Helper;

class FileLogWriter  implements LogWriterInterface
{
    private string $logFilePath;
    private FileFormatter $file_formatter;

    public function __construct(string $logFilePath = '')
    {
        $logFilePath = $logFilePath ?: Helper::getLogFilePath();
        $directory = dirname($logFilePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }
        $this->logFilePath = $logFilePath;
        $this->file_formatter = new FileFormatter();
    }

    public function writeRequest(string $uniqueId, \DateTimeImmutable $startTime, string $method, string $url, array $requestHeaders, string $requestBody): void
    {
        $logEntry = $this->file_formatter->formatRequest(
            $uniqueId,
            $startTime,
            $method,
            $url,
            $requestHeaders,
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
        $logEntry = $this->file_formatter->formatResponse(
            $uniqueId,
            $responseStatusCode,
            $responseHeaders,
            $responseBody,
            $durationMs,
        );

        $r = file_put_contents($this->logFilePath, $logEntry, FILE_APPEND);

        if ($r === false) {
            $error = error_get_last();
            error_log("文件写入失败: " . ($error['message'] ?? '未知错误'));
        }
    }
    
}
