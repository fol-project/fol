<?php
namespace App;

use Fol\Config;
use Fol\Templates;
use Fol\Errors;

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
        //Configure errors
        Errors::register();
        Errors::displayErrors();
        Errors::setPhpLogFile(BASE_PATH.'/logs/php.log');

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
        $this->router = new Router(new RouteFactory($this->getNamespace('Controllers')));

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

        //Register other services
        $this->define([
            'templates' => function () {
                return new Templates($this->getPath('templates'));
            }
        ]);
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

        //Executes the controller
        return $this->router->handle($request, [$this]);
    }
}
