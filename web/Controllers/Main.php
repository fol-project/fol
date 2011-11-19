<?php
namespace Controllers;

class Main extends Base {
	public function index () {
		echo '<link href="/fol/file/index.css" type="text/css" rel="stylesheet" />';
		return;
		$database = $this->Config->get('database');

		$Db = new \Fol\Database($database['default']);

		return;

		$query = $Db->generateSelectQuery(array(
			'data' => array(
				'COUNT(*) as total',
				'posts' => array('id', 'imaxe'),
			),
			'where' => array(
				'posts.id !=' => 45,
				'post.name &' => ':name'
			),
			'limit' => 45,
			'offset' => 3
		));

		$result = $Db->prepare($query);
		$result->bindValue(':name', 'pirolas');
		$result->execute();
		print_r($result->fetchAll());

//		echo $result."<br>";
	}

	public function show ($section, $post = 'default') {
		if ($this->Input->format == 'json') {
			$this->Output->setContentType('json');
			$this->Output->setContent($content);
			//echo json_encode(array('seccion' => $section, 'post' => $post));
			return;
		}

		echo "<p>$section / $post</p>";
	}

	protected function fotos () {
		echo 'fotos';
	}
}
?>