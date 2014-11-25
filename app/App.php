<?php
namespace App;

use Fol\Config;

use Fol\Http\Request;
use Fol\Http\Router\Router;
use Fol\Http\Router\RouteFactory;

class App extends \Fol\App
{
    /**
     * Run the app
     */
    public static function run ()
    {
        //Execute the app
        $app = new static();
        $app(Request::createFromGlobals())->send();
    }


    /**
     * Contructor. Register all services, etc
     */
    public function __construct()
    {
        //Init config
        $this->config = new Config($this->getPath('config'));

        //Init router
        $this->router = new Router(new RouteFactory($this->getNamespace('Controllers'), $this->getUrl()));

        $this->router->map([
            'index' => [
                'path' => '/',
                'target' => 'Index::index'
            ],
            'phpinfo' => [
                'path' => '/phpinfo',
                'target' => 'Index::phpinfo'
            ]
        ]);

        $this->router->setError('Index::error');
    }


    /**
     * Executes a request
     *
     * @param Request $request
     * 
     * @return Response
     */
    public function __invoke(Request $request)
    {
        //Defines the request language
        $request->setLanguage($request->getPreferredLanguage(['gl', 'es', 'en']));

        //Defines the session
        $request->define('session', function () use ($request) {
            return new \Fol\Http\Sessions\Native($request);
        });

        //Executes the controller
        return $this->router->handle($request, [$this]);
    }
}
