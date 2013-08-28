<?php
namespace Apps\Web;

use Fol\Errors;
use Fol\Http\Request;

use Fol\Router\Router;
use Fol\Router\RouteFactory;

class App extends \Fol\App {
	public function __construct () {
		$this->router = new Router(new RouteFactory($this));

		$this->router->map('index', '/', 'Index::index');
		$this->router->map('phpinfo', '/phpinfo', 'Index::phpinfo');
		$this->router->setError('Index::error');

		$this->request = Request::createFromGlobals();
	}

	public function handleRequest (Request $request = null) {
		if ($request === null) {
			$request = $this->request;
		}

		return $this->router->handle($request, $this);
	}
}
?>
