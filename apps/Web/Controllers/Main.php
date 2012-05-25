<?php
namespace Apps\Web\Controllers;

use Fol\Http\Response;

class Main {
	public function index () {
		return new Response('Ola mundo');
	}
}
?>