<?php
namespace Apps\Web\Controllers;

use Fol\Http\Response;

class Index {

	/**
	* @router method get post
	* @router scheme http
	*/
	public function index () {
		return new Response('Ola mundo');
	}

	public function adeus () {
		return new Response('Adeus mundo');
	}

	public function subapp () {
		$App = \Fol\App::create('Web2', $this->App, 'subapp');

		$App->bootstrap();
	}
}
?>