<?php
namespace Apps\File\Controllers;

use Fol\Http\Response;
use Fol\Templates;

class Index {

	public function __construct () {
	}

	public function index () {
		return new Response('Ola mundo');
	}
	public function txts () {
		echo 'ola';
	}

	public function notFound () {
		return new Response('Format not defined', 404);
	}
}
?>