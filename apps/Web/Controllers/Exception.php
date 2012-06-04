<?php
namespace Apps\Web\Controllers;

use Fol\Http\Response;

class Exception {
	public function error ($Exception) {
		return new Response($Exception->getMessage(), 500);
	}

	public function notFound ($Exception) {
		return new Response('Non atopada a páxina', 404);
	}
}
?>