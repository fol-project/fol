<?php
namespace Fol\Http;

use Fol\Http\Headers;
use Fol\Http\Response;
use Fol\Http\Request;
use Fol\Http\HttpException;

class Router {
	private $App;
	private $defaultController;
	private $errorControllers = array();


	/**
	 * public function __construct (Fol\App $App)
	 *
	 * Returns none
	 */
	public function __construct (\Fol\App $App, $defaultController = 'Index') {
		$this->App = $App;
		$this->setDefaultController($defaultController);
	}


	public function setDefaultController ($name) {
		$class = $this->App->getNameSpace().'\\Controllers\\'.$name;

		if (!class_exists($class)) {
			throw new \InvalidArgumentException('The default controller "'.$class.'" is not a valid class');

			return false;
		}

		$Class = new \ReflectionClass($class);

		if (!$Class->isInstantiable()) {
			throw new \InvalidArgumentException('The default controller "'.$class.'" is not instantiable');

			return false;
		}

		$this->defaultController = $Class;
	}



	/**
	 * public function setErrorController ($controller, [$exception], [$code])
	 *
	 * Returns none
	 */
	public function setErrorController ($controller, $exception = 'Exception', $code = 0) {
		if (!isset($this->errorControllers[$exception])) {
			$this->errorControllers[$exception] = array($code => $controller);
		} else {
			$this->errorControllers[$exception][$code] = $controller;
		}
	}



	/**
	 * public function getErrorController (Exception $Exception)
	 *
	 * Returns array/false
	 */
	public function getErrorController (Request $Request, \Exception $Exception) {
		$name = explode('\\', get_class($Exception));
		$name = end($name);

		if (!isset($this->errorControllers[$name])) {
			if ($name === 'Exception' || !isset($this->errorControllers['Exception'])) {
				return false;
			}

			$name = 'Exception';
		}

		$controller = $this->errorControllers[$name];
		$code = $Exception->getCode();

		if (isset($controller[$code])) {
			$controller = $controller[$code];
		} elseif (isset($controller[0])) {
			$controller = $controller[0];
		} else {
			return false;
		}

		if (is_array($controller) === false) {
			$controller = explode('::', $controller);
		}

		if (count($controller) !== 2) {
			throw new Exception('Controller not configurated correctly');

			return false;
		}

		$class = $this->App->getNameSpace().'\\Controllers\\'.ucwords(strtolower($controller[0]));

		$segments = $this->getParametersFromPath($Request->getPath());

		array_unshift($segments, $controller[1]);

		if (class_exists($class) && ($controller = $this->checkControllerMethod($Request, new \ReflectionClass($class), $segments))) {
			$controller[] = array(
				'App' => $this->App,
				'Request' => $Request,
				'Exception' => $Exception
			);

			return $controller;
		}

		return false;
	}


	/**
	 * public function getParametersFromPath ($path)
	 *
	 * Returns array
	 */
	private function getParametersFromPath ($path) {
		$basePath = $this->App->getHttpPath();

		if ($basePath !== '') {
			$path = preg_replace('|^'.preg_quote($basePath).'|', '', $path.'/');
		}

		$segments = array();

		foreach (explode('/', $path) as $segment) {
			if ($segment !== '') {
				$segments[] = $segment;
			}
		}

		return $segments;
	}



	/**
	 * public function getController (Fol\Http\Request $Request)
	 *
	 * Returns array/false
	 */
	public function getController (Request $Request) {
		$segments = $this->getParametersFromPath($Request->getPath());

		if (($controller = $this->checkControllerMethod($Request, $this->defaultController, $segments)) !== false) {
			$controller[] = array(
				'App' => $this->App,
				'Request' => $Request
			);

			return $controller;
		}

		$class = $this->App->getNameSpace().'\\Controllers\\'.str_replace(' ', '', ucwords(strtolower(str_replace('-', ' ', array_shift($segments)))));

		if (class_exists($class) && ($controller = $this->checkControllerMethod($Request, new \ReflectionClass($class), $segments))) {
			$controller[] = array(
				'App' => $this->App,
				'Request' => $Request
			);

			return $controller;
		}

		return false;
	}


	private function checkControllerMethod (Request $Request, \ReflectionClass $Class, array $segments) {
		$method = $segments ? lcfirst(str_replace(' ', '', ucwords(strtolower(str_replace('-', ' ', array_shift($segments)))))) : 'index';

		if (!$Class->isInstantiable() || !$Class->hasMethod($method)) {
			return false;
		}

		if ($this->checkRulesComments($Request, $Class->getDocComment()) === false) {
			return false;
		}

		$Method = $Class->getMethod($method);

		if (!$Method->isPublic()) {
			return false;
		}

		if ($this->checkRulesComments($Request, $Method->getDocComment()) === false) {
			return false;
		}

		if (($parameters = $this->getParameters($Method, $Request, array(), $segments)) === false) {
			return false;
		}

		return array($Class, $Method, $parameters);
	}


	private function parseComments ($comments) {
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


	private function checkRulesComments (Request $Request, $comments) {
		if (!($comments = $this->parseComments($comments)) || !isset($comments['router'])) {
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
	}



	/**
	 * private function getParameters ($controller, Fol\Http\Request $Request, array $parameters, [array $numeric_parameters])
	 *
	 * Returns boolean
	 */
	private function getParameters (\ReflectionMethod $Method, Request $Request, array $parameters, array $numeric_parameters = array()) {
		$new_parameters = array();

		foreach ($Method->getParameters() as $Parameter) {
			$name = $Parameter->getName();

			if ($Request->Parameters->exists($name)) {
				$new_parameters[] = $Request->Parameters->get($name);
			} else if (isset($parameters[$name])) {
				$new_parameters[] = $parameters[$name];
				$Request->Parameters->set($name, $parameters[$name]);
				unset($parameters[$name]);
			} else if (isset($numeric_parameters)) {
				$Request->Parameters->set($name, array_shift($numeric_parameters));
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
	 * public function handle (Fol\Http\Request $Request)
	 *
	 * Executes the controller of the application
	 * Returns none
	 */
	public function handle (Request $Request) {
		try {
			$controller = $this->getController($Request);

			if ($controller === false) {
				throw new HttpException(Headers::$status[404], 404);
			} else {
				$Response = $this->executeController($controller, $Request);
			}
		} catch (\Exception $Exception) {
			if ($controller = $this->getErrorController($Request, $Exception)) {
				$Response = $this->executeController($controller, $Request);
			} else {
				$texto = $Exception->getMessage().'<pre>'.$Exception->getTraceAsString().'</pre>';
				$Response = new Response($texto, $Exception->getCode());
			}
		}

		return $Response;
	}



	/**
	 * public function executeController (array $controller, Fol\Http\Request $Request)
	 *
	 * Executes the controller of the application
	 * Returns none
	 */
	public function executeController (array $controller, Request $Request) {
		ob_start();

		list($Class, $Method, $parameters, $this_parameter) = $controller;

		$Controller = $Class->newInstanceWithoutConstructor();

		foreach ($this_parameter as $name => $this_parameter) {
			$Controller->$name = $this_parameter;
		}

		if ($Class->hasMethod('__construct')) {
			$Controller->__construct();
		}

		$Response = $Method->invokeArgs($Controller, $parameters);

		if (!($Response instanceof Response)) {
			$Response = new Response($Response);
		}

		$Response->appendContent(ob_get_clean());

		return $Response;
	}
}
?>
