<?php
namespace App;

use Fol\Config;
use Fol\Tasks\Runner;
use Fol\Http\Request;
use Fol\Http\Response;
use Fol\Http\MiddlewareStack;
use Fol\Http\Router\Router;
use Fol\Http\Router\RouteFactory;

class App extends \Fol\App
{
    /**
     * Contructor. Register all services, etc
     */
    public function __construct()
    {
        //Init config
        $this->config = new Config($this->getPath('config'));

        //Init router
        $this->register('router', function () {
            $router = new Router(new RouteFactory($this->getNamespace('Controllers')));

            $router->map([
                'index' => [
                    'path' => '/',
                    'target' => 'Index::index',
                ],
                'phpinfo' => [
                    'path' => '/phpinfo',
                    'target' => 'Index::phpinfo',
                ],
            ]);

            $router->setError('Index::error');

            return $router;
        });
    }

    /**
     * Executes a request
     *
     * @param Request $request
     *
     * @return Response
     */
    public function runHttp(Request $request)
    {
        $stack = new MiddlewareStack($this);

        //Request language
        $stack->push(function ($request, $response, $stack) {
            $request->setLanguage($request->getPreferredLanguage(['gl', 'es', 'en']));
            $stack->next();
        });

        //Session
        $stack->push(new \Fol\Http\Sessions\Session());

        //Controller
        $stack->push($this->get('router'));

        $stack->run($request, new Response());

        return $stack->getResponse();
    }

    /**
     * Executes app's tasks
     */
    public function runCli(array $argv)
    {
        Tasks::$app = $this;

        (new Runner())->execute($this->getNamespace('Tasks'), $argv);
    }
}
