<?php

namespace App;

use Fol;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Response\SapiEmitter;
use Zend\Diactoros\Response;
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
        $response = $app->dispatch($request, new Response());

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
        $this->register(new Providers\Middleware());
    }

    /**
     * Executes a request.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     *
     * @return ResponseInterface
     */
    public function dispatch(ServerRequestInterface $request, ResponseInterface $response)
    {
        $adminUrl = env('APP_ADMIN_URL');

        if (strpos((string) $request->getUri(), $adminUrl) === 0) {
            $admin = new Admin($adminUrl, $this);

            return $admin($request);
        }

        $dispatcher = $this->get('middleware');

        return $dispatcher($request, $response);
    }
}
