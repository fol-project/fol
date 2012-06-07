<?php
namespace Apps\Web;

use Fol\Http\Request;

class App extends \Fol\App {
	public function bootstrap () {
		$this->Router = new \Fol\Http\Router($this);

		$this->Router->setErrorController('Errors::generic');
		$this->Router->setErrorController('Errors::notFound', 'HttpException', 404);

		$this->Router->handle(Request::createFromGlobals())->send();
	}
}
?>