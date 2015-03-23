<?php
namespace App;

use Fol\Tasks\Runner;
use Fol\Http\Request;
use Fol\Http\Response;
use Fol\Http\Middlewares;
use Fol\Http\Sessions;
use Fol\Http\Router\Router;
use Fol\Http\Router\RouteFactory;

class App extends \Fol\App
{
    /**
     * Init the app
     */
    protected function init()
    {
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
        $stack = new Middlewares\Middleware();

        //Set the current app
        $stack->setApp($this);

        //Set the base url
        $stack->push(new Middlewares\BaseUrl($this->getUrl()));

        //Detect the client ip
        $stack->push(new Middlewares\Ips());

        //Detect the client language
        $stack->push(new Middlewares\Languages([
            'availables' => 'gl', 'es', 'en'
        ]));

        //Detect the required format (json, html, png, etc...)
        $stack->push(new Middlewares\Formats([
            'fromExtension' => true
        ]));

        //Init the session
        $stack->push(new Sessions\Session());

        //Execute the router
        $stack->push($this->get('router'));

        return $stack->run($request);
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
