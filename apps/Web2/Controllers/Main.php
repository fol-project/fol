<?php
namespace Apps\Web2\Controllers;

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
		return new Response('Ola mundo 2');
	}

	public function adios () {
		var_dump($this->App);
		var_dump($this->App->Parent);
		return new Response('Adeus mundo');
	}

	public function subapp () {
		$App = \Fol\App::create('Web2', $this->App, 'subapp');

		$App->bootstrap();
	}
}
?>