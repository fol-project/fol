<?php
namespace Apps\Web\Controllers;

use Fol\Http\Response;
use Fol\Templates;

class Index {

	public function __construct ($App, $Request) {
		echo '<pre>'.$Request->Headers;
		echo '<pre>'.print_r($Request->Headers->getParsed('accept'), true);
	}

	public function index () {
		return new Response('Ola mundo');
	}
}
?>