<?php
/**
 * Fol\AppsTraits\SimpleRouter
 * 
 * Provides a simple router handler
 */
namespace Fol\AppsTraits;

use Fol\Http\Headers;
use Fol\Http\Request;
use Fol\Http\Response;
use Fol\Http\Router;
use Fol\Http\HttpException;

trait SimpleRouter {

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
			$controller = Router::getController($this, $Request);

			if ($controller === false) {
				throw new HttpException(Headers::$status[404], 404);
			} else {
				$Response = Router::executeController($controller[0], $controller[1], array($this, $Request));
			}
		} catch (\Exception $Exception) {
			$controller = Router::getExceptionController($this, $Request, $Exception);

			if ($controller === false) {
				$Response = new Response($Exception->getMessage(), $Exception->getCode() ?: null);
			} else {
				$Response = Router::executeController($controller[0], $controller[1], array($this, $Request));
			}
		}

		return $Response;
	}
}
?>
