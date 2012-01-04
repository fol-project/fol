<?php
namespace Apps\Web\Controllers;

use Fol\Response;
use Fol\Controller;
use Fol\App;

class Main extends Controller {
	public function index () {
		$App = App::create('Web2', $this->App);

		$this->Request->setUrl(implode('/', $this->Request->Path->getNumerical()));

		return $App->execute($this->Request);
	}
}
?>