<?php
namespace Apps\Web\Controllers;

use Fol\Controller;
use Fol\Http\Response;

class Main extends Controller {

	public function index () {
		$contido = $this->Views->render('html.php', array(
			'titulo' => 'Ola mundo',
			'estilos' => 'css/csans.css'
		));

		return new Response($contido);
	}
}
?>