<?php
namespace Apps\Web\Controllers;

use Fol\Http\Response;

/**
*
* @method GET
*/
class Main {

	/**
	* @method GET, POST
	* @scheme http
	* @ajax true
	*/
	public function index () {
		return new Response('Ola mundo');
	}

	public function adios () {
		return new Response('Adeus mundo');
	}
}
?>