<?php
namespace Controllers;

class Main extends Base {
	public function index () {
	}

	public function show ($section, $post = 'default') {
		echo "<p>$section / $post</p>";
	}
}
?>