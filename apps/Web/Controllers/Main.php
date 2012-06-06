<?php
namespace Apps\Web\Controllers;

use Fol\Http\Response;

/**
*
* @router method get
*/
class Main {

	/**
	* @router method get post
	* @router scheme http
	*/
	public function index () {
		var_dump($this->App->session);
		return new Response('Ola mundo');
	}

	public function adios () {
		return new Response('Adeus mundo');
	}

	public function subapp () {
		$App = \Fol\App::create('Web2', $this->App, 'subapp');

		$App->bootstrap();
	}
}
?>