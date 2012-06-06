<?php
namespace Apps\Web2;

use Fol\Http\Request;

class App extends \Fol\App {
	public function bootstrap () {
		$this->Router = new \Fol\Http\Router($this, 'Main');

		$this->Router->setExceptionsControllers(array(
			'HttpException' => array(
				404 => array('Exception', 'notFound'),
			),
			'Exception' => array(
				0 => array('Exception', 'error')
			)
		));

		$this->Router->handle(Request::createFromGlobals())->send();
	}
}
?>