<?php
namespace App;

use Fol;
use Relay\Relay;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Response\SapiEmitter;
use Zend\Diactoros\Response;
use Psr7Middlewares\Middleware;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class App extends Fol
{
    /**
     * Run the app
     */
    public static function run()
    {
        $app = new static();

        $request = ServerRequestFactory::fromGlobals();
        $response = $app->dispatch($request);

        (new SapiEmitter())->emit($response);
    }

    /**
     * Init the app
     */
    public function __construct()
    {

    }

    /**
     * Executes a request
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function dispatch(ServerRequestInterface $request)
    {
        $dispatcher = new Relay([
            Middleware::ClientIp(),
            Middleware::LanguageNegotiator(),
            function ($request, $response) {
                $response->getBody()->write('Ola mundo');

                return $response;
            }
        ]);

        return $dispatcher($request, new Response());
    }
}
