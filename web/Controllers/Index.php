<?php
namespace Apps\Web\Controllers;

use Fol\Http\Response;
use Fol\Http\HttpException;

class Index {
	public function __construct ($App) {
		$this->App = $App;
	}

	public function index ($Request) {
		echo '<html><body>';
		echo '<h1>FOL funciona estupendamente <br>(ou acaso o dubidabas?)</h1>';
		echo '<p><a href="phpinfo">Ver o phpinfo</a></p>';
		echo '</body></html>';

	}

	public function phpinfo ($Request) {
		phpinfo();
	}

	public function files ($Request) {
		$file = preg_replace('#^'.preg_quote($this->App->assetsUrl.'/cache/', '#').'#', '', $Request->getUrl(false));
		$method = $Request->getFormat();
	}

	public function error ($Request, HttpException $Exception) {
		return new Response($Exception->getMessage(), 500);
	}
}
?>
