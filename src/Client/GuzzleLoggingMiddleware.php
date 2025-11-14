<?php


namespace Qscmf\HttpTracer\Client;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Qscmf\HttpTracer\Lib\RequestLogger;

class GuzzleLoggingMiddleware
{
    private RequestLogger $logger;

    public function __construct(RequestLogger $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke(callable $handler): callable
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            $start_time = microtime(true);
            
            $trace_id = $this->logger->start(
                $request->getMethod(),
                (string) $request->getUri(),
                $request->getHeaders(),
                (string) $request->getBody()
            );

            $request = $request->withHeader('X-Trace-ID', $trace_id);
            $promise = $handler($request, $options);

            return $promise->then(
                function (ResponseInterface $response) use ($trace_id, $start_time) {
                    $duration = (microtime(true) - $start_time) * 1000;
                    $body_content = $response->getBody()->getContents();
                    $response->getBody()->rewind();
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
}