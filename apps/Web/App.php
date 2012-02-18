<?php
namespace Apps\Web;

use Fol\Http\Request;

class App extends \Fol\App {

	public function bootstrap () {
		//Define services to use
		$this->Services->register('Config', 'Fol\\Config', array($this->path.'config/'));
		$this->Services->register('Router', 'Fol\\Http\\Router', array($this));

		//Define the routes
		$this->Router->registerException($this->Config->get('exceptionRoutes'));
		$this->Router->register($this->Config->get('routes'));

		//Handle the request
		$this->Router->handle(Request::createFromGlobals())->send();
	}
}
?>