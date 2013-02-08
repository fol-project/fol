<?php
namespace Apps\Web;

use Fol\Http\Router;
use Fol\Http\Request;

class App extends \Fol\App {
	public function handle () {
		$Request = Request::createFromGlobals();

		return Router::handle($this, $Request, [$this, $Request], [$Request]);
	}
}
?>
