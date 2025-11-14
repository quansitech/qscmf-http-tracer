<?php

namespace Qscmf\HttpTracer\Lib\LogWriter;

use Qscmf\HttpTracer\Lib\FileFormatter;
use Qscmf\HttpTracer\Lib\Helper;

class FileLogWriter  implements LogWriterInterface
{
    private string $log_file_path;
    private FileFormatter $file_formatter;

    public function __construct(string $log_file_path = '')
    {
        $log_file_path = $log_file_path ?: Helper::getLogFilePath();
        $directory = dirname($log_file_path);
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }
        $this->log_file_path = $log_file_path;
        $this->file_formatter = new FileFormatter();
    }

    public function writeRequest(string $trace_id, \DateTimeImmutable $start_time, string $method, string $url, array $request_headers, string $request_body): void
    {
        $log_entry = $this->file_formatter->formatRequest(
            $trace_id,
            $start_time,
            $method,
            $url,
            $request_headers,
            $request_body
        );

        $r = file_put_contents($this->log_file_path, $log_entry, FILE_APPEND);

        if ($r === false) {
            $error = error_get_last();
            error_log("文件写入失败: " . ($error['message'] ?? '未知错误'));
        }
    }

    public function writeResponse(string $trace_id, int $response_status_code, array $response_headers, string $response_body, float $duration_ms): void
    {
        $log_entry = $this->file_formatter->formatResponse(
            $trace_id,
            $response_status_code,
            $response_headers,
            $response_body,
            $duration_ms,
        );

        $r = file_put_contents($this->log_file_path, $log_entry, FILE_APPEND);

        if ($r === false) {
            $error = error_get_last();
            error_log("文件写入失败: " . ($error['message'] ?? '未知错误'));
        }
    }
    
}
