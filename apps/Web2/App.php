<?php
namespace Apps\Web2;

use Fol\Http\Request;

class App extends \Fol\App {
	public function bootstrap () {
		$this->Router = new \Fol\Http\Router($this);

		$this->Router->handle(Request::createFromGlobals())->send();
	}
}
?>