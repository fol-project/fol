<?php
namespace Apps\Web;

use Fol\Http\Router;
use Fol\Http\Request;

class App extends \Fol\App {
	public function __construct () {
		$this->router->map('index', '/', 'Index::index');
		$this->router->map('phpinfo', '/phpinfo', 'Index::phpinfo');
	}
}
?>
