<?php
namespace Apps\Web\Controllers;

use Fol\Http\Response;

class Errors {
	public function exception () {
		return new Response($this->Exception->getMessage(), 500);
	}

	public function httpException_404 ($word) {
		return new Response('Page not found', 404);
	}
}
?>