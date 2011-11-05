<?php
namespace Controllers;

class Main extends Base {
	public function index () {
		echo 'index';
	}

	public function show ($section, $post = 'default') {
		echo "<p>$section / $post</p>";
	}
}
?>