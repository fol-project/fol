<?php
namespace Apps\Web2\Controllers;

use Fol\Http\Response;

class Index {

	/**
	 * @router method get post
	 * @router scheme http
	 */
	public function index () {
		return new Response('Ola mundo 2');
	}

	public function adios () {
		return new Response('Adeus mundo 2');
	}
}
?>