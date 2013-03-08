<?php
namespace Apps\Web\Controllers;

use Fol\Http\Response;
use Fol\Http\HttpException;

class Index {
	public function __construct ($App) {
	}

	public function index ($Request) {
		return new Response('Ola mundo');
	}

	public function error ($Request, HttpException $Exception) {
		return new Response($Exception->getMessage(), 500);
	}
}
?>
