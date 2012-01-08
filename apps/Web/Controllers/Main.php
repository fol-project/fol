<?php
namespace Apps\Web\Controllers;

use Fol\Response;
use Fol\Controller;
use Fol\App;

class Main extends Controller {
	public function index () {
		$content = $this->Views->render('html.php', array('variable' => 'mola'));

		$Response = new Response($content);

		$this->App->Cache->File->set($this->Request->getId(), $Response);

		return $Response;
	}

	public function testSpeed () {
		$repeticions = 10000;
		
		$time = microtime(true);

		for ($n = 0; $n < $repeticions; $n++) {
			include($this->App->path.'config/controllers.php');
		}

		echo '<p>Total caso 1: '.(microtime(true) - $time).'</p>';

		$time = microtime(true);

		for ($n = 0; $n < $repeticions; $n++) {
			parse_ini_file($this->App->path.'config/controllers.ini', true);
		}

		echo '<p>Total caso 2: '.(microtime(true) - $time).'</p>';
	}

	public function show ($section) {
		return new Response("ola $section, que tal");
	}
}
?>