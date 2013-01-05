<?php
/**
 * Fol\Http\Router
 * 
 * Class to convert http request to a class name
 */
namespace Fol\Http;

use Fol\Http\Headers;
use Fol\Http\Response;
use Fol\Http\Request;
use Fol\Http\HttpException;

class Router {

	/**
	 * Returns a controller according to the request data
	 * 
	 * For example, for the request with url: post/list
	 * Search for the method [app_namespace]\Controllers\Index::post(list)
	 * If it does not exists, search for [app_namespace]\Controllers\Post::list()
	 *
	 * @param Fol\App $App The instance of the application
	 * @param Fol\Http\Request $Request The request used
	 * 
	 * @return array The controller data (an array with the controller and the arguments) or false
	 */
	static public function getController (\Fol\App $App, Request $Request) {
		$segments = $Request->getPathSegments($App->url);

		$arguments = $segments;
		$controller = self::checkController($Request, $App->namespace.'\\Controllers\\Index', ($arguments ? array_shift($arguments) : 'index'));

		if ($controller !== false) {
			$controller[2] = $arguments;

			return $controller;
		}

		if ($segments) {
			$class = $App->namespace.'\\Controllers\\'.str_replace(' ', '', ucwords(strtolower(str_replace('-', ' ', array_shift($segments)))));
			$controller = self::checkController($Request, $class, ($segments ? array_shift($segments) : 'index'));

			if ($controller !== false) {
				$controller[2] = $segments;

				return $controller;
			}
		}

		return false;
	}


	/**
	 * Returns a the error controller according to the request data
	 * 
	 * @param Fol\App $App The instance of the application
	 * @param Fol\Http\Request $Request The request used
	 * 
	 * @return array The controller data (an array with the controller and the arguments) or false
	 */
	static public function getErrorController (\Fol\App $App, Request $Request) {
		$segments = $Request->getPathSegments($App->url);

		if ($segments) {
			$arguments = $segments;
			$class = $App->namespace.'\\Controllers\\'.str_replace(' ', '', ucwords(strtolower(str_replace('-', ' ', array_shift($segments)))));
			$controller = self::checkController($Request, $class, 'error');

			if ($controller !== false) {
				$controller[2] = $segments;

				return $controller;
			}
		}

		$controller = self::checkController($Request, $App->namespace.'\\Controllers\\Index', 'error');

		if ($controller !== false) {
			$controller[2] = $segments;

			return $controller;
		}

		return false;
	}


	/**
	 * Public function to check if a method (controller) is callable
	 * 
	 * @param Fol\Http\Request $Request The request used (used to check the request properties: method, if it's ajax, etc)
	 * @param ReflectionClass $Class The class reflection instance (used to check if it's instantiable)
	 * @param string $method The method name of the controller
	 * 
	 * @return array The controller data (class, method and parameters)
	 */
	static public function checkController (Request $Request, $class, $method) {
		if (!class_exists($class)) {
			return false;
		}

		$Class = new \ReflectionClass($class);
		$method = lcfirst(str_replace(' ', '', ucwords(strtolower(str_replace('-', ' ', $method)))));

		if (!$Class->isInstantiable() || !$Class->hasMethod($method) || (self::checkRulesComments($Request, $Class->getDocComment()) === false)) {
			return false;
		}

		$Method = $Class->getMethod($method);

		if (!$Method->isPublic() || (self::checkRulesComments($Request, $Method->getDocComment()) === false)) {
			return false;
		}

		return array($Class, $Method);
	}


	/**
	 * Private function to parse the comments of a class, function or method
	 * 
	 * @param string $comments The comments to parse
	 * 
	 * @return array The comments data with the comments labels (for example: @router) or FALSE on error
	 */
	static private function parseComments ($comments) {
		if (empty($comments)) {
			return false;
		}

		if (preg_match('#^/\*\*(.*)\*/#s', $comments, $comments) === false) {
			return false;
		}

		if (preg_match_all('#^[\s\*]+(.*)#m', $comments[1], $comments) === false) {
			return false;
		}

		$info = array();

		foreach ($comments[1] as $line) {
			if (!preg_match('/^@([\w]+)\s+(.*)$/', $line, $line)) {
				continue;
			}

			$name = $line[1];
			$value = trim($line[2]);

			if (!isset($info[$name])) {
				$info[$name] = array($value);
			} else {
				$info[$name][] = $value;
			}
		}

		return $info;
	}


	/**
	 * Private function to check if the rules of the controller match with the request
	 * 
	 * @param Fol\Http\Request $Request The request instance
	 * @param string $comments The comments to check
	 * 
	 * @return boolean TRUE if the controller is valid, false if not
	 */
	static private function checkRulesComments (Request $Request, $comments) {
		if (!($comments = self::parseComments($comments)) || !isset($comments['router'])) {
			return true;
		}

		foreach ($comments['router'] as $rule) {
			$rule = explode(' ', strtolower($rule), 2);

			if (!isset($rule[1])) {
				continue;
			}

			$values = explode(' ', $rule[1]);

			switch ($rule[0]) {
				case 'method':
					$value = $Request->getMethod();
					break;

				case 'scheme':
					$value = $Request->getScheme();
					break;

				case 'port':
					$value = $Request->getPort();
					break;

				case 'ip':
					$value = $Request->getIp();
					break;

				case 'ajax':
					$value = ($Request->isAjax() === true) ? 'true' : 'false';
					break;

				default:
					continue 2;
			}

			if (!in_array($value, $values)) {
				return false;
			}
		}

		return true;
	}



	/**
	 * Executes a controller
	 * 
	 * @param array $controller The controller data
	 * @param array $constructor_args The arguments for the controller constructor
	 * @param array $controller_args The arguments for the controller method
	 * 
	 * @return Fol\Http\Response The response of the controller
	 */
	static public function executeController (array $controller, array $constructor_args, array $controller_args) {
		ob_start();

		list($Class, $Method) = $controller;

		$Response = $Method->invokeArgs($Class->newInstanceArgs($constructor_args), $controller_args);

		if (!($Response instanceof Response)) {
			$Response = new Response($Response);
		}

		$Response->prependContent(ob_get_clean());

		return $Response;
	}


	/**
	 * Handle a http request: search a controller and execute it
	 * 
	 * @param Fol\App $App The instance of the application
	 * @param Fol\Http\Request $Request The request object
	 * @param array $constructor_args The arguments for the controller constructor
	 * @param array $controller_args The arguments for the controller method
	 * 
	 * @return Fol\Http\Response The response object with the controller result
	 */
	static public function handle (\Fol\App $App, Request $Request, array $constructor_args = array(), array $controller_args = array()) {
		try {
			if (($controller = self::getController($App, $Request)) === false) {
				throw new HttpException(Headers::$status[404], 404);
			} else {
				$Request->Parameters->set($controller[2]);
				$Response = self::executeController($controller, $constructor_args, $controller_args);
			}
		} catch (\Exception $Exception) {
			if (($controller = self::getErrorController($App, $Request)) === false) {
				$Response = new Response($Exception->getMessage(), $Exception->getCode() ?: null);
			} else {
				$Request->Parameters->set($controller[2]);
				array_unshift($controller_args, $Exception);
				$Response = self::executeController($controller, $constructor_args, $controller_args);
			}
		}

		return $Response;
	}
}
?>
