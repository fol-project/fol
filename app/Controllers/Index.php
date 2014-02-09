<?php
namespace App\Controllers;

use Fol\Http\Response;
use Fol\Http\HttpException;

class Index {
	public function index ($request) {
		$templates = $this->app->templates;

		$templates->saveRender('content', '<h1>Ola mundo!!</h1><p><a href="phpinfo">Ver o phpinfo</a></p>');

		return $templates->render('html.php');
	}

	public function phpinfo ($request) {
		phpinfo();
	}

	//Files preprocessor
	public function file ($request) {
		$cached = $request->getPath(true);
		$origin = str_replace('/cache/', '/', $cached);

		echo $request->getFormat();
		echo "$cached \n $origin \n";
	}

	//Error controller
	public function error ($request, $response) {
		$exception = $request->parameters->get('exception');

		$response->setContent($exception->getMessage());
	}
}
