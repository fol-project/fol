<?php
namespace Apps\Web;

use Fol\Http\Request;

class App extends \Fol\App {

	public function bootstrap (Request $Request = null) {

		$routes = new \Fol\Http\Router();

		$routes->register(array(
			array(
				'pattern' => '/ola',
				'controller' => function ($Request) {
					echo 'caracola '.$Request->Get->get('nome');
				}
			)
		));

		$routes->handle(Request::createFromGlobals())->send();

		return;


		$this->Services->register('Router', 'Fol\\Http\\Router', array($this));

		//Define services to use
		$this->Services->register('Config', 'Fol\\Config', array($this->path.'config/'));
		

		//Define the routes
		$this->Router->registerException($this->Config->get('exceptionRoutes'));
		$this->Router->register($this->Config->get('routes'));

		//Handle the request
		$this->Router->handle($Request ?: Request::createFromGlobals())->send();
	}
}
?>