<?php
namespace Apps\Web;

use Fol\Http\Request;

class App extends \Fol\App {

	public function init () {
		$this->Services->register('Router', 'Fol\\Http\\Router', array($this));
		$this->Services->register('Config', 'Fol\\Config', array($this->path.'config/'));
		$this->Services->register('Models', 'Fol\\Models');
		$this->Services->register('Views', 'Fol\\Views');

		$this->Router->registerException($this->Config->get('exceptionRoutes'));
		$this->Router->register($this->Config->get('routes'));
	}

	public function bootstrap () {
		$this->init();

		$this->Router->handle(Request::createFromGlobals())->send();
	}
}
?>