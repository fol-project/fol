<?php
namespace Apps\Web\Controllers;

use Fol\Http\Response;
use Fol\Http\HttpException;

class Index {
	public function __construct ($App, $Request) {
		$this->App = $App;
		$this->Request = $Request;
	}

	public function index () {
		return new Response('Ola mundo');
	}

	public function error (HttpException $Exception) {
		return new Response($Exception->getMessage(), 500);
	}

	public function error404 (HttpException $Exception) {
		return new Response('Esta páxina non existe', 404);
	}
}
?>