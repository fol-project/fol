<?php
namespace Fol\Http;

use Fol\Http\Headers;
use Fol\Http\Response;
use Fol\Http\Request;
use Fol\Http\HttpException;

class Router {
	private $App;
	private $defaultController;
	private $exceptionControllers = array();


	/**
	 * public function __construct (Fol\App $App)
	 *
	 * Returns none
	 */
	public function __construct (\Fol\App $App, $defaultController = 'Main') {
		$this->App = $App;
		$this->setDefaultController($defaultController);
	}


	public function setDefaultController ($name) {
		$class = $this->App->getNameSpace().'\\Controllers\\'.$name;

		if (!class_exists($class)) {
			throw new InvalidArgumentException('The default controller "'.$class.'" is not a valid class');

			return false;
		}

		$Class = new \ReflectionClass($class);

		if (!$Class->isInstantiable()) {
			throw new InvalidArgumentException('The default controller "'.$class.'" is not instantiable');

			return false;
		}

		$this->defaultController = $Class;
	}



	/**
	 * public function setExceptionsControllers ($controllers)
	 *
	 * Returns none
	 */
	public function setExceptionsControllers (array $controllers) {
		$this->exceptionControllers = array_replace_recursive($this->exceptionControllers, $controllers);
	}



	/**
	 * public function getExceptionController (Exception $Exception)
	 *
	 * Returns array/false
	 */
	public function getExceptionController (Request $Request, \Exception $Exception) {
		$name = explode('\\', get_class($Exception));
		$name = end($name);

		if (!isset($this->exceptionControllers[$name])) {
			if ($name === 'Exception' || !isset($this->exceptionControllers['Exception'])) {
				return false;
			}

			$name = 'Exception';
		}

		$controller = $this->exceptionControllers[$name];
		$code = $Exception->getCode();

		if (isset($controller[$code])) {
			$controller = $controller[$code];
		} elseif (isset($controller[0])) {
			$controller = $controller[0];
		} else {
			return false;
		}

		$class = $this->App->getNameSpace().'\\Controllers\\'.ucwords(strtolower($controller[0]));

		if (!class_exists($class)) {
			throw new InvalidArgumentException('The exception controller "'.$class.'" is not valid');

			return false;
		}

		return $this->checkControllerMethod($Request, new \ReflectionClass($class), array($controller[1], $Exception));
	}



	/**
	 * public function getController (Fol\Http\Request $Request)
	 *
	 * Returns array/false
	 */
	public function getController (Request $Request) {
		$segments = $Request->getPathSegments();

		if (($controller = $this->checkControllerMethod($Request, $this->defaultController, $segments)) !== false) {
			return $controller;
		}

		$class = $this->App->getNameSpace().'\\Controllers\\'.str_replace(' ', '', ucwords(strtolower(str_replace('-', ' ', array_shift($segments)))));

		if (class_exists($class)) {
			return $this->checkControllerMethod($Request, new \ReflectionClass($class), $segments);
		}

		return false;
	}


	private function checkControllerMethod (Request $Request, \ReflectionClass $Class, array $segments) {
		$method = $segments ? lcfirst(str_replace(' ', '', ucwords(strtolower(str_replace('-', ' ', array_shift($segments)))))) : 'index';

		if (!$Class->isInstantiable() || !$Class->hasMethod($method)) {
			return false;
		}

		$Method = $Class->getMethod($method);

		if (!$Method->isPublic()) {
			return false;
		}

		$config = $this->parseComments($Method->getDocComment());

		if (isset($config['method']) && !in_array($Request->getMethod(), $config['method'])) {
			return false;
		}

		if (($parameters = $this->getParameters($Method, $Request, array(), $segments)) === false) {
			return false;
		}

		return array($Class, $Method, $parameters);
	}


	private function parseComments ($comments) {
		if (!$comments) {
			return array();
		}

		if (preg_match('#^/\*\*(.*)\*/#s', $comments, $comments) === false) {
			return array();
		}

		if (preg_match_all('#^\s*\*(.*)#m', trim($comments[1]), $lines) === false) {
			return array();
		}

		$docs = array();

		foreach ($lines[1] as $line) {
			$line = trim($line);

			if ($line === '' || preg_match('/^@([\w]+)\s+(.*)\s*$/', $line, $rule) === false) {
				continue;
			}

			switch ($rule[2]) {
				case 'true':
					$docs[$rule[1]] = true;
					break;

				case 'false':
					$docs[$rule[1]] = false;
					break;

				default:
					$docs[$rule[1]] = explode(',', str_replace(' ', '', $rule[2]));
					break;
			}
		}

		return $docs;
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
			if ($controller = $this->getExceptionController($Request, $Exception)) {
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

		list($Class, $Method, $parameters) = $controller;

		$Controller = $Class->newInstance();
		$Controller->App = $this->App;
		$Controller->Request = $Request;

		$Response = $Method->invokeArgs($Controller, $parameters);

		if (!($Response instanceof Response)) {
			$Response = new Response($Response);
		}

		$Response->appendContent(ob_get_clean());

		return $Response;
	}
}
?>