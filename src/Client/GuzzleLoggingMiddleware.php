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
            $startTime = microtime(true);
            
            $uniqueId = $this->logger->start(
                $request->getMethod(),
                (string) $request->getUri(),
                $request->getHeaders(),
                (string) $request->getBody()
            );

            $request = $request->withHeader('X-Trace-ID', $uniqueId);
            $promise = $handler($request, $options);

            return $promise->then(
                function (ResponseInterface $response) use ($uniqueId, $startTime) {
                    $duration = (microtime(true) - $startTime) * 1000;
                    $bodyContent = $response->getBody()->getContents();
                    // After getting contents, the stream is empty. We need to rewind it.
                    $response->getBody()->rewind();
                    $this->logger->finish(
                        $uniqueId,
                        $response->getStatusCode(),
                        $response->getHeaders(),
                        $bodyContent,
                        $duration
                    );
                    return $response;
                },
                function (\Exception $reason) use ($uniqueId, $startTime) {
                    $duration = (microtime(true) - $startTime) * 1000;
                    $this->logger->finish(
                        $uniqueId,
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