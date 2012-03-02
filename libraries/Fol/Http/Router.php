<?php
namespace Fol\Http;

use Fol\Http\Headers;
use Fol\Http\Response;
use Fol\Http\Request;
use Fol\Http\HttpException;

class Router {
	public $namespace;
	public $unregisteredRoutes = true;

	private $routes;
	private $exceptionRoutes;
	private $App;


	/**
	 * public function __construct ([Object $App])
	 *
	 * Returns none
	 */
	public function __construct ($App = null) {
		$this->App = $App;
		$this->namespace = $this->App->namespace.'\\Controllers\\';
	}



	/**
	 * public function register (string $name, string $pattern, callable $controller, [array $parameters])
	 * public function register (string $name, array $route)
	 * public function register (array $routes)
	 *
	 * Returns none
	 */
	public function register ($name = null, $route = null, $controller = null, array $parameters = array()) {
		if (is_array($name)) {
			foreach ($name as $n => $v) {
				$this->register($n, $v);
			}

			return;
		}

		if (!is_array($route)) {
			$route = array(
				'pattern' => $route,
				'controller' => $controller,
				'parameters' => $parameters
			);
		}

		if (!$route['controller']) {
			throw new \InvalidArgumentException("You must define a controller for the route '$name'");
		}

		if (!$route['pattern']) {
			throw new \InvalidArgumentException("You must define a pattern for the route '$name'");
		}

		if (!isset($route['parameters'])) {
			$route['parameters'] = array();
		}

		$route['pattern'] = $this->preparePattern($route['pattern'], $route['parameters']);

		if (is_array($route['controller'])) {
			$route['controller'][0] = $this->namespace.$route['controller'][0];
		}

		$this->routes[$name] = $route;
	}



	/**
	 * private function preparePattern (string $pattern, [array $parameters])
	 *
	 * Returns string
	 */
	private function preparePattern ($pattern, array $parameters = array()) {
		if (strpos($pattern, '(') === false) {
			return $pattern;
		}

		if ($parameters) {
			$pattern = preg_replace('#(\('.implode('|', array_keys($parameters)).'(\s+[^\)]+)?\)[^?])#', '\\1?', $pattern);
		}

		return preg_replace_callback('#/\((\w+)(\s+[^\)]+)?\)\??#', array($this, 'matchCallback'), $pattern);
	}



	/**
	 * public function registerException (string $name, callable $controller, [int $code])
	 *
	 * Returns none
	 */
	public function registerException ($name = null, $controller = null, $code = null) {
		if (is_array($name)) {
			foreach ($name as $value) {
				$this->registerException($value['name'], $value['controller'], $value['code']);
			}

			return;
		}

		if (!$controller) {
			throw new \InvalidArgumentException("You must define a controller for the exception route '$name'");
		}

		if (is_array($controller)) {
			$controller[0] = $this->namespace.$controller[0];
		}

		$this->exceptionRoutes["$name $code"] = $controller;
	}




	/**
	 * public function getExceptionController (Exception $Exception)
	 *
	 * Returns array/false
	 */
	public function getExceptionController ($Exception) {
		$name = end(explode('\\', get_class($Exception)));
		$code = $Exception->getCode();

		$controller = $this->exceptionRoutes["$name $code"] ?: $this->exceptionRoutes[$name];

		if (!$controller) {
			return false;
		}

		if ($this->isCallable($controller)) {
			return array($controller, array($Exception));
		}

		return false;
	}



	/**
	 * public function getController (Fol\Http\Request $Request)
	 *
	 * Returns array/false
	 */
	public function getController (Request $Request) {
		if ($this->unregistered_routes) {
			return $this->getRoutingController($Request) ?: $this->getUnregisteredController($Request);
		}

		return $this->getRoutingController($Request);
	}



	/**
	 * public function getUnregisteredController (Fol\Http\Request $Request)
	 *
	 * Returns array/false
	 */
	public function getUnregisteredController (Request $Request) {
		$segments = $Request->getPathSegments();

		if (!$segments) {
			return false;
		}

		$controller = array($this->namespace.camelCase(array_shift($segments), true));
		$controller[1] = $segments ? camelCase(array_shift($segments)) : 'index';

		if ($this->isCallable($controller) && ($parameters = $this->getParameters($controller, $Request, array(), $segments)) !== false) {
			return array($controller, $parameters);
		}

		return false;
	}



	/**
	 * public function getRoutingController (Fol\Http\Request $Request)
	 *
	 * Check the route and returns the controller
	 * Returns array/false
	 */
	public function getRoutingController (Request $Request) {
		if (!$this->routes) {
			return false;
		}

		$path = '/'.$Request->getPath();

		foreach ($this->routes as $name => $settings) {
			if ($settings['method'] && ($Request->getMethod() !== $settings['method'])) {
				continue;
			}

			if ($settings['scheme'] && ($Request->getScheme() !== $settings['scheme'])) {
				continue;
			}

			if (($parameters = $this->matchParameters($path, $settings)) !== false) {
				if ($this->isCallable($settings['controller']) && ($parameters = $this->getParameters($settings['controller'], $Request, $parameters)) !== false) {
					return array($settings['controller'], $parameters);
				}

				return false;
			}
		}

		return false;
	}



	/**
	 * private function getParameters ($controller, Fol\Http\Request $Request, array $parameters, [array $numeric_parameters])
	 *
	 * Returns boolean
	 */
	private function getParameters ($controller, Request $Request, array $parameters, array $numeric_parameters = array()) {
		$new_parameters = array();

		if (is_array($controller)) {
			$Method = new \ReflectionMethod($controller[0], $controller[1]);
		} else {
			$Method = new \ReflectionFunction($controller);
		}

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
	 * private function isCallable ($controller)
	 *
	 * Returns boolean
	 */
	private function isCallable ($controller) {
		if (is_callable($controller)) {
			return true;
		}

		if (class_exists($controller[0])) {
			$Class = new \ReflectionClass($controller[0]);

			if ($Class->isInstantiable() && $Class->hasMethod($controller[1]) && $Class->getMethod($controller[1])->isPublic()) {
				return true;
			}
		}

		return false;
	}



	/**
	 * private function matchParameters (string $path, array $route)
	 *
	 * Returns boolean
	 */
	private function matchParameters ($path, array $route) {
		if (preg_match('#^'.$route['pattern'].'$#', $path, $matches)) {
			$return = $route['parameters'];

			foreach ($matches as $name => $value) {
				if (is_string($name)) {
					$return[$name] = $value;
					next($matches);
				}
			}

			return $return;
		}

		return false;
	}



	/**
	 * private function matchCallback (array $matches)
	 *
	 * Returns string
	 */
	private function matchCallback ($matches) {
		if (!$matches[2]) {
			$matches[2] = '[^/]+';
		}

		if (substr($matches[0], -1) === '?') {
			return '/?(?P<'.$matches[1].'>'.trim($matches[2]).')?';
		}

		return '/(?P<'.$matches[1].'>'.trim($matches[2]).')';
	}



	/**
	 * public function handle (Fol\Http\Request $Request)
	 *
	 * Executes the controller of the application
	 * Returns none
	 */
	public function handle (Request $Request) {
		try {
			if ($controller = $this->getController($Request)) {
				$Response = $this->executeController($controller, $Request);
			} else {
				throw new HttpException(Headers::$status[404], 404);
			}
		}

		catch (HttpException $Exception) {}
		catch (\ErrorException $Exception) {}

		if (isset($Exception)) {
			if ($controller = $this->getExceptionController($Exception)) {
				$Response = $this->executeController($controller, $Request);
			} else {
				$Response = new Response($Exception->getMessage(), $Exception->getCode());
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
		if ($controller) {
			if (is_array($controller[0])) {
				list($class, $method) = $controller[0];

				$Controller = new $class($this->App, $Request);

				if ($controller[1]) {
					$Response = call_user_func_array(array($Controller, $method), $controller[1]);
				} else {
					$Response = $Controller->$method();
				}
			} else {
				array_unshift($controller[1], $Request);
				$Response = call_user_func_array($controller[0], $controller[1]);
			}

			if (!($Response instanceof Response)) {
				$Response = new Response($Response);
			}

			return $Response;
		}

		return false;
	}
}
?>