<?php
/**
 * Fol\Utils\Router
 * 
 * Provides a simple router handler
 */
namespace Fol\Utils;

use Fol\Http\Headers;
use Fol\Http\Request;
use Fol\Http\Response;
use Fol\Http\Router as HttpRouter;
use Fol\Http\HttpException;

trait Router {

	/**
	 * Handle a http request
	 * 
	 * $app->handle($Request);
	 * $app->handle('my/path', 'POST', array('name' => 'value'));
	 * 
	 * @param Fol\Http\Request $Request The request object used or the path of the request
	 * @param string $method The method used (if we defined a path as $Request)
	 * @param array $parameters The variables of the request (if we defined a path as $Request)
	 * 
	 * @return Fol\Http\Response The response object with the controller result
	 */
	public function handle ($Request = null, $method = 'GET', array $parameters = array()) {
		if (func_num_args() === 0) {
			$Request = Request::createFromGlobals();
		} else if (!is_object($Request) || get_class($Request) !== 'Fol\\Http\\Request') {
			$Request = Request::create($Request, $method, $parameters);
		}

		try {
			if (($controller = HttpRouter::getController($this, $Request)) === false) {
				throw new HttpException(Headers::$status[404], 404);
			} else {
				$Request->Parameters->set($controller[2]);
				$Response = HttpRouter::executeController($controller, array($this), array($Request));
			}
		} catch (\Exception $Exception) {
			if (($controller = HttpRouter::getController($this, $Request)) === false) {
				$Response = new Response($Exception->getMessage(), $Exception->getCode() ?: null);
			} else {
				$Request->Parameters->set($controller[2]);
				$Response = HttpRouter::executeController($controller, array($this), array($Request, $Exception));
			}
		}

		return $Response;
	}
}
?>
