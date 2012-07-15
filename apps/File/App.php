<?php
namespace Apps\File;

use Fol\Http\Request;

class App extends \Fol\App {
	public function bootstrap () {
		$this->Router = new \Fol\Http\Router($this);

		$this->Router->setErrorController('Index::notFound');

		$Request = Request::createFromGlobals();

		$Request->Parameters->set('file', $Request->getPath());
		$Request->setPath($Request->getFormat());

		$this->Router->handle($Request)->send();
	}
}
?>
