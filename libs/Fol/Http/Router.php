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
	 * Returns a controller according to a Exception
	 * The class search for the [app_namespace]\Controllers\Errors class and a method with the name of the Exception name
	 * 
	 * For example, an HttpException with code 404 (page not found):
	 * Search for the method [app_namespace]\Controllers\Errors::HttpException404
	 * If does not exists, search for the method [app_namespace]\Controllers\Errors::HttpException
	 * If does not exists, search for the method [app_namespace]\Controllers\Errors::Exception
	 * 
	 * @param Fol\App $App The instance of the application
	 * @param Fol\Http\Request $Request The request used
	 * @param Exception $Exception The exception used
	 * 
	 * @return array The controller data (an array with the controller and the arguments) or false
	 */
	static public function getErrorController (\Fol\App $App, Request $Request, \Exception $Exception) {
		$class = $App->namespace.'\\Controllers\\Errors';

		if (class_exists($class) === false) {
			return false;
		}

		$class = new \ReflectionClass($class);

		$name = explode('\\', get_class($Exception));
		$name = end($name);

		if ($Exception->getCode() && ($controller = self::checkControllerMethod($Request, $class, $name.$Exception->getCode())) !== false) {
			return $controller;
		}

		if (($controller = self::checkControllerMethod($Request, $class, $name)) !== false) {
			return $controller;
		}

		if (($name !== 'Exception') && ($controller = self::checkControllerMethod($Request, $class, $name)) !== false) {
			return $controller;
		}

		return false;
	}


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

		$class = $App->namespace.'\\Controllers\\Index';
		$method = $segments ? lcfirst(str_replace(' ', '', ucwords(strtolower(str_replace('-', ' ', array_shift($segments)))))) : 'index';

		if (class_exists($class) && ($controller = self::checkControllerMethod($Request, new \ReflectionClass($class), $method, $segments)) !== false) {
			return $controller;
		}

		$class = $App->namespace.'\\Controllers\\'.str_replace(' ', '', ucwords(strtolower(str_replace('-', ' ', array_shift($segments)))));
		$method = $segments ? lcfirst(str_replace(' ', '', ucwords(strtolower(str_replace('-', ' ', array_shift($segments)))))) : 'index';

		if (class_exists($class) && ($controller = self::checkControllerMethod($Request, new \ReflectionClass($class), $method, $segments))) {
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
	 * @param array $parameters The parameters used to call the method
	 * 
	 * @return array The controller data or FALSE if it's not callable
	 */
	static public function checkControllerMethod (Request $Request, \ReflectionClass $Class, $method, array $parameters = array()) {
		if (!$Class->isInstantiable() || !$Class->hasMethod($method)) {
			return false;
		}

		if (self::checkRulesComments($Request, $Class->getDocComment()) === false) {
			return false;
		}

		$Method = $Class->getMethod($method);

		if (!$Method->isPublic()) {
			return false;
		}

		if (self::checkRulesComments($Request, $Method->getDocComment()) === false) {
			return false;
		}

		if (($parameters = self::getParameters($Method, $Request, $parameters)) === false) {
			return false;
		}

		return array($Method, $parameters);
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
	 * Private function to sort the parameters used in a function in the apropiate order
	 * 
	 * @param ReflectionFunctionAbstract $Function An instance of the ReflectionFunction or ReflectionMethod class
	 * @param Fol\Http\Request $Request The request (used to take custom parameters by name)
	 * @param array $parameters The parameters in a numerical array (taken from the request path)
	 * 
	 * @return array Numerical array with the arguments in the apropiate order of false if some required arguments are missing
	 */
	static private function getParameters (\ReflectionFunctionAbstract $Function, Request $Request, array $parameters) {
		$new_parameters = array();

		foreach ($Function->getParameters() as $Parameter) {
			$name = $Parameter->getName();

			if ($Request->Parameters->exists($name)) {
				$new_parameters[] = $Request->Parameters->get($name);
			} else if (isset($parameters[0])) {
				$Request->Parameters->set($name, array_shift($parameters));
				$new_parameters[] = $Request->Parameters->get($name);
			} else if ($Parameter->isOptional()) {
				$new_parameters[] = $Parameter->getDefaultValue();
			} else {
				return false;
			}
		}

		return array_merge($new_parameters, array_values($parameters));
	}



	/**
	 * Executes a controller
	 * 
	 * @param ReflectionFunctionAbstract $Controller The ReflectionFunction or ReflectionMethod instance with the constructor data
	 * @param array $parameters The parameters used to call the constructor
	 * @param array $constructor_parameters If the controller is a method, the parameters used in the constructor of the class
	 * 
	 * @return Fol\Http\Response The response of the controller
	 */
	static public function executeController (\ReflectionFunctionAbstract $Controller, array $parameters = null, array $constructor_parameters = null) {
		ob_start();

		if (isset($Controller->class)) {
			if (isset($constructor_parameters)) {
				$class = $Controller->getDeclaringClass()->newInstanceArgs($constructor_parameters);
			} else {
				$class = $Controller->getDeclaringClass()->newInstance();
			}

			$Response = $Controller->invokeArgs($class, $parameters);
		} else {
			$Response = $Constructor->invokeArgs($parameters);
		}

		if (!($Response instanceof Response)) {
			$Response = new Response($Response);
		}

		$Response->prependContent(ob_get_clean());

		return $Response;
	}
}
?>
