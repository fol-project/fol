<?php
namespace Apps\Web\Controllers;

use Fol\Http\Response;
use Fol\Http\HttpException;

class Index {
	public function __construct ($App) {
	}

	public function index ($Request) {
		return new Respons('Ola mundo');
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
