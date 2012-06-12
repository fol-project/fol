<?php
namespace Apps\Web;

use Fol\Http\Request;

class App extends \Fol\App {
	public function bootstrap () {
		$this->Router = new \Fol\Http\Router($this);

		$this->Router->setErrorController('Errors::generic');
		$this->Router->setErrorController('Errors::notFound', 'HttpException', 404);

		$this->handle()->send();
	}

	public function handle ($url = null, $method = 'GET', array $parameters = array()) {
		if (!isset($url)) {
			$Request = Request::createFromGlobals();
		} else if (is_string($url)) {
			$Request = Request::create($url, $method, $parameters);
		}

		return $this->Router->handle($Request);
	}
}
?>