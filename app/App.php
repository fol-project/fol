<?php

namespace App;

use Fol;
use Relay\RelayBuilder;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Response\SapiEmitter;
use Zend\Diactoros\Response;
use Psr7Middlewares\Middleware;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class App extends Fol
{
    /**
     * Run the app.
     */
    public static function run()
    {
        $app = new static();

        $request = ServerRequestFactory::fromGlobals();
        $response = $app->dispatch($request);

        (new SapiEmitter())->emit($response);
    }

    /**
     * Init the app.
     */
    public function __construct()
    {
        $this->setPath(dirname(__DIR__));
        $this->setUrl(env('APP_URL'));

        $this->register(new Providers\Router());
        $this->register(new Providers\Templates());
    }

    /**
     * Executes a request.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function dispatch(ServerRequestInterface $request)
    {
        $dispatcher = (new RelayBuilder())->newInstance([
            Middleware::ClientIp(),
            Middleware::FormatNegotiator(),
            Middleware::AuraRouter($this['router'])->arguments($this),
        ]);

        return $dispatcher($request, new Response());
    }
}
