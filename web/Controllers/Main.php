<?php
namespace Controllers;

class Main extends Base {
	public function index () {
	}

	public function seccion ($seccion = 'default') {
		echo "<p>Estamos na seccion $seccion</p>";
	}
}
?>