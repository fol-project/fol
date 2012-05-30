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
		return new Response('Ola mundo web2');
	}

	public function adios () {
		return new Response('Adeus mundo web2');
	}
}
?>