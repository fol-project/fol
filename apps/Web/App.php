<?php
namespace Apps\Web;

use Fol\Http\Request;

class App extends \Fol\App {

	public function bootstrap () {
		//Define services to use
		$this->Services->register('Config', 'Fol\\Config', array($this->path.'config/'));
		$this->Services->register('Router', 'Fol\\Router', array($this));
		$this->Services->register('Views', 'Fol\\Views');
		$this->Services->register('Models', 'Fol\\Models');

		//Config router
		$this->Router->setNamespace(__NAMESPACE__.'\\Controllers');
		$this->Router->setConfig($this->Config->get('controllers'));

		//Handle the request
		$this->Router->handle(Request::createFromGlobals())->send();
	}
}
?>