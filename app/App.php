<?php
namespace App;

use Fol;
use Relay\Relay;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Response\SapiEmitter;
use Zend\Diactoros\Response;
use Psr7Middlewares\Middleware;
use Psr\Http\Message\ServerRequestInterface;
use Fol\Tasks\Runner;

class App extends Fol
{
    /**
     * Run the app in a http server context
     */
    public static function runHttp()
    {
        $app = new static();

        $request = ServerRequestFactory::fromGlobals();
        $response = $app->execHttpRequest($request);

        (new SapiEmitter())->emit($response);
    }

    /**
     * Run the app in a cli context
     */
    public static function runCli()
    {
        $app = new static();
        $app->execCommand($_SERVER['argv']);
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
     * @param Request $request
     *
     * @return Response
     */
    public function execHttpRequest(ServerRequestInterface $request)
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

    /**
     * Executes app's tasks
     * 
     * @param array $argv
     */
    public function execCommand(array $argv)
    {
        Tasks::$app = $this;

        (new Runner())->execute($this->getNamespace('Tasks'), $argv);
    }
}
