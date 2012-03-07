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

	public function twitter ($query) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, 'http://search.twitter.com/search.json?q='.$query);
		curl_setopt($curl, CURLOPT_PORT, 80);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		$body = curl_exec($curl);
		curl_close($curl);

		return new Response('<pre>'.$body.'</pre>');
	}
}
?>