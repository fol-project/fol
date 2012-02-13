<?php
namespace Fol;

use Fol\Http\Response;
use Fol\Http\Request;
use Fol\Http\HttpException;

class Router {
	private $namespace;
	private $config = array();
	private $App;


	/**
	 * public function __construct ([Object $App])
	 *
	 * Returns none
	 */
	public function __construct ($App = null) {
		$this->App = $App;
	}



	/**
	 * public function setNamespace (String $namespace)
	 *
	 * Returns none
	 */
	public function setNamespace ($namespace) {
		if ($namespace && substr($namespace, -1) !== '\\') {
			$this->namespace = $namespace.'\\';
		} else {
			$this->namespace = $namespace;
		}
	}



	/**
	 * public function getNamespace ()
	 *
	 * Returns string
	 */
	public function getNamespace () {
		return $this->namespace;
	}


	/**
	 * public function setConfig (string $name, mixed $value)
	 * public function setConfig (array $config)
	 *
	 * Returns none
	 */
	public function setConfig ($config, $value = null) {
		if (is_array($config)) {
			$this->config = array_replace($this->config, $config);
		} else {
			$this->config[$config] = $value;
		}
	}


	/**
	 * public function getConfig ([string $name])
	 *
	 * Returns none
	 */
	public function getConfig ($name = null) {
		if (func_num_args() === 0) {
			return $this->config;
		}

		return $this->config[$name];
	}



	/**
	 * public function getExceptionController (Exception $Exception)
	 *
	 * Returns array/false
	 */
	public function getExceptionController ($Exception) {
		$name = end(explode('\\', get_class($Exception)));

		if (($controller = $this->config['exceptions'][$name])) {
			$controller = explode(':', $controller, 2);

			$controller[0] = $this->namespace.camelCase($controller[0], true);

			if ($this->isCallable($controller)) {
				return array($controller, array($Exception));
			}
		}

		return false;
	}



	/**
	 * public function getController (Fol\Http\Request $Request)
	 *
	 * Returns array/false
	 */
	public function getController (Request $Request) {
		if ($this->config['allow_undefined_routings']) {
			return $this->getRoutingController($Request) ?: $this->getPathController($Request);
		}

		return $this->getRoutingController($Request);
	}



	/**
	 * public function getPathController (Fol\Http\Request $Request)
	 *
	 * Returns array/false
	 */
	public function getPathController (Request $Request) {
		$segments = $Request->getPathSegments();

		if ($segments) {
			$class = $this->namespace.camelCase($segments[0], true);

			if (class_exists($class)) {
				array_shift($segments);
				$controller = array($class, ($segments ? camelCase(array_shift($segments)) : 'index'));
			} else {
				$controller = array($this->namespace.$this->config['default'], camelCase(array_shift($segments)));
			}
		} else {
			$controller = array($this->namespace.$this->config['default'], 'index');
		}

		if ($this->isCallable($controller) && ($parameters = $this->getParameters($controller, $Request, array(), $segments)) !== false) {
			return array($controller, $parameters);
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

		$Method = new \ReflectionMethod($controller[0], $controller[1]);

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
		if (!class_exists($controller[0])) {
			return false;
		}

		$Class = new \ReflectionClass($controller[0]);

		if (!$Class->isInstantiable() || !$Class->hasMethod($controller[1]) || !$Class->getMethod($controller[1])->isPublic()) {
			return false;
		}

		return true;
	}



	/**
	 * public function getRoutingController (Fol\Http\Request $Request)
	 *
	 * Check the route and returns the controller
	 * Returns array/false
	 */
	public function getRoutingController (Request $Request) {
		if (!$this->config['routing']) {
			return false;
		}

		$path = $Request->getPath();

		foreach ($this->config['routing'] as $name => $settings) {
			if (!is_array($settings)) {
				$settings = array(
					'pattern' => $name,
					'controller' => $settings
				);
			}

			if ($settings['method'] && ($Request->getMethod() !== $settings['method'])) {
				continue;
			}

			if ($settings['scheme'] && ($Request->getScheme() !== $settings['scheme'])) {
				continue;
			}

			if ($parameters = $this->matchParameters($path, $settings)) {
				$controller = explode(':', $settings['controller'], 2);
				$controller[0] = $this->namespace.camelCase($controller[0], true);

				if ($this->isCallable($controller) && ($parameters = $this->getParameters($controller, $Request, $parameters)) !== false) {
					return array($controller, $parameters);
				}

				return false;
			}
		}

		return false;
	}



	/**
	 * private function matchParameters (string $path, array $route)
	 *
	 * Returns boolean
	 */
	private function matchParameters ($path, $route) {
		if (strpos($route['pattern'], '(') === false) {
			if (preg_match('|^'.preg_quote($route['pattern'], '|').'$|', $path, $matches)) {
				return (array)$route['parameters'];
			}

			return false;
		}

		if ($route['parameters']) {
			$route['pattern'] = preg_replace('#(\('.implode('|', array_keys($route['parameters'])).'(\s+[^\)]+)?\)[^?])#', '\\1?', $route['pattern']);
		}

		$route['pattern'] = preg_replace_callback('#/\((\w+)(\s+[^\)]+)?\)\??#', array($this, 'matchCallback'), $route['pattern']);

		if (preg_match('|^'.$route['pattern'].'$|', $path, $matches)) {
			$return = (array)$route['parameters'];

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
				throw new HttpException(Response::$status[404], 404);
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
	 * public function executeController ($controller, Fol\Http\Request $Request)
	 *
	 * Executes the controller of the application
	 * Returns none
	 */
	public function executeController (array $controller, Request $Request) {
		if ($controller) {
			list($class, $method) = $controller[0];

			$Controller = new $class($this->App, $Request);

			if ($controller[1]) {
				$Response = call_user_func_array(array($Controller, $method), $controller[1]);
			} else {
				$Response = $Controller->$method();
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