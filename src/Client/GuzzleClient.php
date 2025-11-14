<?php

namespace Qscmf\HttpTracer\Client;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Qscmf\HttpTracer\Lib\RequestLogger;

class GuzzleClient
{
    private RequestLogger $logger;

    public function __construct(RequestLogger $logger)
    {
        if (!class_exists(Client::class)) {
            throw new \LogicException(
                'Guzzle is not installed. Please run "composer require guzzlehttp/guzzle" to use the GuzzleClient.'
            );
        }
        $this->logger = $logger;
    }

    public function create(array $config = []): Client
    {
        $stack = HandlerStack::create();
        $middleware = new GuzzleLoggingMiddleware($this->logger);
        $stack->push($middleware, 'logger');
        $final_config = array_merge($config, ['handler' => $stack]);
        return new Client($final_config);
    }
}