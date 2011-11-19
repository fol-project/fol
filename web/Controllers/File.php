<?php
namespace Controllers;

use Fol\Controller;

class File extends Controller {
	public function css () {
		$file = implode('/', $this->Router->path).'.'.$this->Input->format;
		print_r($file);
	}

	public function js ($text) {
		echo "<p>500: $text</p>";
	}
}
?>