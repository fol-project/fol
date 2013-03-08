<?php
namespace Apps\Web;

use Fol\Http\Router;
use Fol\Http\Request;

class App extends \Fol\App {
	public function __construct () {
		$this->Router = new Router($this->url);

		$this->Router->map('/', 'Index::index');
	}

	public function handle () {
		$Request = Request::createFromGlobals();

		return $this->Router->handle($this, $Request);
	}
}
?>
