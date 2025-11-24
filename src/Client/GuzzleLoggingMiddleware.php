<?php


namespace Qscmf\HttpTracer\Client;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Qscmf\HttpTracer\Lib\RequestLogger;
use Psr\Http\Message\StreamInterface;

class GuzzleLoggingMiddleware
{
    private RequestLogger $logger;
     private const MAX_LOG_SIZE = 1024 * 1024; 

    public function __construct(RequestLogger $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke(callable $handler): callable
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            $start_time = microtime(true);
            $request_body = $this->formatBody(
                $this->readStream($request->getBody()), 
                $request->getHeader('Content-Type')[0] ?? ''
            );
            
            $trace_id = $this->logger->start(
                $request->getMethod(),
                (string) $request->getUri(),
                $request->getHeaders(),
                $request_body
            );

            $request = $request->withHeader('X-Trace-ID', $trace_id);
            $promise = $handler($request, $options);

            return $promise->then(
                function (ResponseInterface $response) use ($trace_id, $start_time) {
                    $duration = (microtime(true) - $start_time) * 1000;

                    $body_content = $this->formatBody(
                        $this->readStream($response->getBody()), 
                        $response->getHeader('Content-Type')[0] ?? ''
                    );

                    $this->logger->finish(
                        $trace_id,
                        $response->getStatusCode(),
                        $response->getHeaders(),
                        $body_content,
                        $duration
                    );
                    return $response;
                },
                function (\Exception $reason) use ($trace_id, $start_time) {
                    $duration = (microtime(true) - $start_time) * 1000;
                    $this->logger->finish(
                        $trace_id,
                        0,
                        [],
                        $reason->getMessage(),
                        $duration
                    );
                    throw $reason;
                }
            );
        };
    }

     /**
     * 安全地读取流内容
     * 既防止了破坏不可倒带的流（导致请求失败），也防止了内存溢出。
     */
    private function readStream(StreamInterface $stream): string
    {
        // 1. 如果流不可倒带（例如上传大文件），千万别读！读了就回不去了，请求会报错。
        if (!$stream->isSeekable()) {
            return '[Stream not seekable - Content ignored]';
        }

        // 2. 如果流太大（超过阈值），也不要读，防止日志撑爆内存。
        $size = $stream->getSize();
        if ($size !== null && $size > self::MAX_LOG_SIZE) {
            return "[Stream too large ({$size} bytes) - Content ignored]";
        }

        // 3. 安全读取：读取 -> 转字符串 -> 立即倒带
        $content = (string) $stream;
        $stream->rewind();

        return $content;
    }

    /**
     * 格式化 Body：处理编码转换和二进制过滤
     */
    private function formatBody(string $content, string $content_type): string
    {
        if ($content === '') {
            return '';
        }

        // 1. 二进制检查：如果是图片/文件，直接返回摘要，节省内存且防止乱码
        if ($this->isBinary($content, $content_type)) {
            $size = strlen($content);
            return "[Binary Data - Size: {$size} bytes - Type: {$content_type}]";
        }

        // 2. mb_check_encoding 转换操作
        if (mb_check_encoding($content, 'UTF-8')) {
            return $content;
        }

        // 3. 乱码修复路径：如果不是 UTF-8，尝试用常见的中文编码进行挽救
        return mb_convert_encoding($content, 'UTF-8', 'GBK, GB18030, BIG5');
    }

    /**
     * 判断是否为二进制内容
     */
    private function isBinary(string $content, string $content_type): bool
    {
        // 1. 白名单策略：只要 Content-Type 包含这些关键词，就认为是文本
        if (preg_match('/(json|xml|html|text|form-data|javascript|ecmascript)/i', $content_type)) {
            return false;
        }

        // 2. 如果 Content-Type 是空的 (或未识别的)，通过检查内容的前 512 字节来判断
        if ($content !== '') {
            // 如果包含大量不可打印字符(控制字符)，认为是二进制
            return preg_match('/[^\x20-\x7E\t\r\n]/', substr($content, 0, 512)) > 0;
        }

        // 3. 默认有 Content-Type 但不在白名单里 (如 image/png)，认为是二进制
        return true;
    }

}