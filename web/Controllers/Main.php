<?php
namespace Controllers;

class Main extends Base {
	public function index () {
		$this->Templates->setRender('content', 'content.php');

		echo $this->Templates->render('html.php');
	}

	public function show ($section, $post = 'default') {
		echo "<p>$section / $post</p>";
	}
}
?>