<?php
namespace Controllers;

class Main {
	public function index () {
		echo '<p>Estamos en index</p>';
	}

	public function seccion ($seccion = 'default') {
		echo "<p>Estamos na seccion $seccion</p>";
	}
}
?>