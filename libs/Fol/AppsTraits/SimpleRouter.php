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

		$segments = $Request->getPathSegments($this->url);

		$parameters = $segments;
		$class = $this->namespace.'\\Controllers\\Index';
		$method = $parameters ? lcfirst(str_replace(' ', '', ucwords(strtolower(str_replace('-', ' ', array_shift($parameters)))))) : 'index';

		$controller = Router::checkController($Request, $class, $method, $parameters);

		if (($controller === false) && $segments) {
			$parameters = $segments;
			$class = $this->namespace.'\\Controllers\\'.str_replace(' ', '', ucwords(strtolower(str_replace('-', ' ', array_shift($parameters)))));
			$method = $parameters ? lcfirst(str_replace(' ', '', ucwords(strtolower(str_replace('-', ' ', array_shift($parameters)))))) : 'index';

			$controller = Router::checkController($Request, $class, $method, $parameters);
		}

		try {
			if ($controller === false) {
				throw new HttpException(Headers::$status[404], 404);
			} else {
				$Response = Router::executeController($controller, array($this, $Request));
			}
		} catch (HttpException $Exception) {
			$controller = false;

			if ($segments) {
				$parameters = array($Exception);
				$class = $this->namespace.'\\Controllers\\'.str_replace(' ', '', ucwords(strtolower(str_replace('-', ' ', $segments[0]))));
				$method = 'error'.$Exception->getCode();

				$controller = Router::checkController($Request, $class, $method, $parameters);

				if ($controller === false) {
					$method = 'error';

					$controller = Router::checkController($Request, $class, $method, $parameters);
				}
			}

			if ($controller === false) {
				$parameters = array($Exception);
				$class = $this->namespace.'\\Controllers\\Index';
				$method = 'error'.$Exception->getCode();

				$controller = Router::checkController($Request, $class, $method, $parameters);

				if ($controller === false) {
					$method = 'error';

					$controller = Router::checkController($Request, $class, $method, $parameters);
				}
			}

			if ($controller === false) {
				$Response = new Response($Exception->getMessage(), $Exception->getCode() ?: null);
			} else {
				$Response = Router::executeController($controller, array($this, $Request));
			}
		}

		return $Response;
	}
}
?>
