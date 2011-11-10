<?php
namespace Controllers;

class Main extends Base {
	public function index () {
		/*
		$database = $this->Config->get('database');

		$Db = new \Fol\Database\Mysql($database['default']);

		$result = $Db->select(array(
			'fields' => array(
				'posts' => array('id', 'title', 'posts'),
				'comments' => array('id', 'text')
			),
			'conditions' => array(
				'posts.id' => 45
			),
			'limit' => 45,
			'offset' => 3
		));
		*/

		$this->Templates->render('content', 'content.php');
		$content = $this->Templates->render('base');
		$this->Output->setContentType('html');
		$this->Output->setContent('ola, que tal');

		//print_r($result);

	}

	public function show ($section, $post = 'default') {
		if ($this->Input->format == 'json') {
			echo json_encode(array('seccion' => $section, 'post' => $post));
			return;
		}

		echo "<p>$section / $post</p>";
	}
}
?>