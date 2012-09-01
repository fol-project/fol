<?php
namespace Apps\File\Controllers;

use Fol\Http\Response;
use Fol\Templates;

class Index {

	public function __construct () {
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