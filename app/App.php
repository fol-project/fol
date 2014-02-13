<?php
namespace App;

use Fol\Config;
use Fol\Templates;

use Fol\Http\Request;
use Fol\Http\RequestStack;

use Fol\Router\Router;
use Fol\Router\RouteFactory;

class App extends \Fol\App {
	public function __construct () {

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


		//Register other classes
		$this->register([
			'templates' => function () {
				return new Templates($this->getPath('templates'));
			},
			'requestStack' => function () {
				return new RequestStack;
			}
		]);
	}


	//Request handler
	public function __invoke ($request = null) {
		if ($request === null) {
			$request = Request::createFromGlobals();
		}

		$this->requestStack->push($request);
		$response = $this->router->handle($request, $this);
		$this->requestStack->pop();

		return $response;
	}
}
