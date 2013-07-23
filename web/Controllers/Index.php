<?php
namespace Apps\Web\Controllers;

use Fol\Http\Response;
use Fol\Http\HttpException;

class Index {
	public function index ($request) {
		echo '<html><body>';
		echo '<h1>Ola mundo!!</h1>';
		echo '<p><a href="phpinfo">Ver o phpinfo</a></p>';
		echo '</body></html>';
	}

	public function phpinfo ($request) {
		phpinfo();
	}

	public function error ($request, $response) {
		$exception = $request->Parameters->get('exception');

		$response->setStatus($exception->getCode() ?: 500);
		$response->setContent($exception->getMessage());
	}
}
?>
