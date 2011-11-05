<?php
namespace Controllers;

class Main extends Base {
	public function index () {
	}

	public function seccion ($fixo, $variable = 'default') {
		echo "<p>$fixo / $variable</p>";
	}
}
?>