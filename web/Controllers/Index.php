<?php
namespace Apps\Web\Controllers;

use Fol\Http\Response;
use Fol\Http\HttpException;

class Index {
	public function index ($Request) {
		echo '<html><body>';
		echo '<h1>Ola mundo!!</h1>';
		echo '<p><a href="phpinfo">Ver o phpinfo</a></p>';
		echo '</body></html>';
	}

	public function phpinfo ($Request) {
		phpinfo();
	}

	public function error ($Request, $Response) {
		$Exception = $Request->Parameters->get('Exception');

		$Response->setStatus($Exception->getCode() ?: 500);
		$Response->setContent($Exception->getMessage());
	}
}
?>
