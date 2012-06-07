<?php
namespace Apps\Web\Controllers;

use Fol\Http\Response;

class Errors {
	public function generic () {
		return new Response($this->Exception->getMessage(), 500);
	}

	public function notFound ($word) {
		return new Response('Page not found', 404);
	}
}
?>