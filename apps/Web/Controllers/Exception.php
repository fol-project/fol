<?php
namespace Apps\Web\Controllers;

use Fol\Http\Response;

class Exception {
	public function http ($Exception) {
		return new Response($Exception->getMessage(), 404);
	}

	public function error ($Exception) {
		return new Response($Exception->getMessage(), 500);
	}
}
?>