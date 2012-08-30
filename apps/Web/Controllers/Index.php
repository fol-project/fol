<?php
namespace Apps\Web\Controllers;

use Fol\Http\Response;
use Fol\Templates;

class Index {

	public function __construct ($App, $Request) {
		$this->App = $App;
		$this->Request = $Request;
	}

	public function index () {
		return new Response('Ola mundo');
	}
}
?>
