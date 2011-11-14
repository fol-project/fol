<?php
namespace Controllers;

class Main extends Base {
	public function index () {
		return;
		$database = $this->Config->get('database');

		$Db = new \Fol\Database($database['default']);

		$scheme = $Db->getScheme();
		print_r($scheme);
return;
		$scheme['tags'] = array(
			'columns' => array(
				array(
					'Field' => 'id',
					'Type' => 'int(8)',
					'Null' => 'NO',
					'Key' => 'PRI',
					'Default' => '',
					'Extra' => 'auto_increment'
				)
			)
		);

		$scheme['post2'] = $scheme['posts'];
		$update = $Db->generateUpdateSchemeQuery($scheme);

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