<?php
namespace Apps\Web;

use Fol\Http\Router;
use Fol\Http\Request;

class App extends \Fol\App {
	public function __construct () {
		$this->Router = new Router($this->url);

		$this->Router->map('index', '/', 'Index::index');
		$this->Router->map('phpinfo', '/phpinfo', 'Index::phpinfo');
	}

	public function handle (Request $Request, $name = null) {
		return $this->handleRequest($this->Router, $Request, $name);
	}
}
?>
