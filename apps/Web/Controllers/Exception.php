<?php
namespace Apps\Web\Controllers;

use Fol\Response;

class Exception {
	public function notFound ($Exception) {
		return new Response($Exception->getMessage, 404);
	}

	public function serverError ($Exception) {
		return new Response($Exception->getMessage, 500);
	}

	public function others ($Exception) {
		return new Response($Exception->getMessage, $Exception->getCode());
	}
}
?>