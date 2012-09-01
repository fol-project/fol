<?php
namespace Apps\File\Controllers;

use Fol\Http\Response;

class Index {
	private $cache = true;

	public function __construct ($App, $Request) {
		$this->App = $App;
		$this->Request = $Request;
	}

	private function cachePath ($file) {
		$path = dirname($file);

		if (!is_dir($this->App->assetsPath.'cache/'.$path)) {
			mkdir($this->App->assetsPath.'cache/'.$path, 0777, true);
		}

		return $this->App->assetsPath.'cache/'.$file;
	}
}
?>