<?php
namespace Apps\Web\Controllers;

use Fol\Http\Response;
use Fol\Templates;

class Index {

	public function __construct () {
		$this->Templates = new Templates($this->App->getPath().'templates');
		$this->Templates->App = $this->App;
		$this->Mustache = new \Mustache();
	}

	public function index () {
		return new Response('Ola mundo');
	}

	public function layout ($name) {
		if (!($layout_dir = $this->getLayoutPath($name))) {
			return new Response('This layout does not exists', 404);
		}

		$options = array_replace_recursive(include($layout_dir.'options.php'), $this->Request->Get->get());

		$data = array(
			'name' => $name,
			'options' => $options
		);

		$this->Templates->register('layout', $layout_dir.'html.php');

		return new Response($this->Templates->render('layout.php', $data));
	}

	public function styles ($name) {
		if (!($layout_dir = $this->getLayoutPath($name))) {
			return new Response('This layout does not exists', 404);
		}

		$data = array_replace_recursive(include($layout_dir.'options.php'), $this->Request->Get->get());

		$styles = $this->Mustache->render(file_get_contents($layout_dir.'styles.mustache'), $data);

		$response = new Response($styles);
		$response->setContentType('text/css');

		return $response;
	}

	private function getLayoutPath ($name) {
		$path = $this->App->getPath().'templates/layouts/'.strtolower($name);

		if (!is_dir($path)) {
			return false;
		}

		return $path.'/';
	}
}
?>