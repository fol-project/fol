<?php
namespace Controllers;

class Main extends Base {
	public function index () {
		echo $this->Templates->render('base');
	}

	public function show ($section, $post = 'default') {
		echo "<p>$section / $post</p>";
	}
}
?>