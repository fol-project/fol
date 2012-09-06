<?php
/**
 * Fol\AppsTraits\PreprocessedFileRouter
 * 
 * Provides a simple router handler for preprocessed files
 */
namespace Fol\AppsTraits;

use Fol\Http\Headers;
use Fol\Http\Request;
use Fol\Http\Response;
use Fol\Http\Router;
use Fol\Http\HttpException;

trait PreprocessedFileRouter {

	/**
	 * Handle a http request
	 * 
	 * $app->handle($Request);
	 * 
	 * @param Fol\Http\Request $Request The request object used or the path of the request
	 * 
	 * @return Fol\Http\Response The response object with the controller result
	 */
	public function handleFile (Request $Request) {
		$file = preg_replace('#^'.preg_quote($this->assetsUrl.'cache/', '#').'#', '', $Request->getFullPath(true));
		$class = $this->namespace.'\\Controllers\\Files';

		if (class_exists($class)) {
			$controller = Router::checkControllerMethod($Request, new \ReflectionClass($class), $Request->getFormat(), array($file));

			try {
				if ($controller === false) {
					$Response = new Response('File cannot be preprocessed', 404);
				} else {
					$Response = Router::executeController($controller[0], $controller[1], array($this, $Request));
				}
			} catch (\Exception $Exception) {
				$Response = new Response($Exception->getMessage(), $Exception->getCode());
			}
		} else {
			$Response = new Response('Controllers not defined', 404);
		}

		return $Response;
	}
}
?>
