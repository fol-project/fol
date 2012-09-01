<?php
namespace Apps\Web\Controllers;

use Fol\Http\HttpException;
use Fol\Http\Response;
use Fol\Templates;

class Index {

	public function __construct ($App, $Request) {
		$this->App = $App;
		$this->Request = $Request;

		$this->Templates = new Templates($this->App->path.'templates');
		$this->Templates->App = $App;
		$this->Templates->Controller = $this;

		$this->Galleries = new \Apps\Web\Models\Galleries($this->App->assetsPath.'fotos/');
		$this->titulo = 'Fotos de prendas';
	}


	public function index () {
		$this->Templates->register('body', 'body-index.php');

		return $this->Templates->render('html.php', array(
			'titulo' => $this->titulo,
			'data' => array(
				'titulo' => $this->titulo,
				'galerias' => $this->Galleries->get()
			)
		));
	}


	public function galeria ($nome) {
		if (!$this->Galleries->exists($nome)) {
			throw new HttpException("Esta galería non existe", 404);
		}

		$this->Templates->register('body', 'body-galeria.php');

		return $this->Templates->render('html.php', array(
			'titulo' => $this->titulo.' » '.$nome,
			'data' => array(
				'galeria' => array(
					'nome' => $nome
				),
				'fotos' => $this->Galleries->getPhotos($nome)
			)
		));
	}


	/**
	 * @router method post
	 */
	public function novaGaleria () {
		$nome = $this->Request->Post->get('nome');

		$this->Galleries->create($nome);

		$Response = new Response;
		$Response->redirect($this->App->url.'galeria/'.$nome);

		return $Response;
	}

	
	/**
	 * @router method post
	 */
	public function subirFotos () {
		$nome = urldecode($this->Request->Post->get('nome'));
		$foto = $this->Request->Files->get('userfile');

		$this->Galleries->uploadPhoto($nome, $foto);
	}
}
?>
