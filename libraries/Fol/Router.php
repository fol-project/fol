<?php
namespace Fol;

class Router {

	/**
	 * static function getExceptionController (string $name_app, Object $Exception, string $controller)
	 *
	 * Check and returns the controller for a exception
	 * Returns array/false
	 */
	static function getExceptionController ($name_app, $Exception, $controller) {
		if ($controller) {
			$namespace = 'Apps\\'.camelCase($name_app, true).'\\Controllers\\';

			list($class, $method) = explode(':', $controller, 2);

			$class = $namespace.camelCase($class, true);

			if (class_exists($class)) {
				return self::checkController($class, $method, array($Exception));
			}
		}

		return false;
	}



	/**
	 * static function getController (string $name_app, Fol\Request $Request, [array $config])
	 *
	 * Check the route and returns the controller
	 * Returns array/false
	 */
	static function getController ($name_app, Request $Request, array $config = array()) {
		$namespace = 'Apps\\'.camelCase($name_app, true).'\\Controllers\\';
		$path = $Request->getPath();

		if ($config['routing']) {
			foreach ($config['routing'] as $name => $settings) {
				if (!is_array($settings)) {
					$settings = array(
						'pattern' => $name,
						'controller' => $settings
					);
				}

				if ($match = self::match($path, $settings)) {
					list($class, $method) = explode(':', $settings['controller'], 2);

					$class = $namespace.camelCase($class, true);

					if (class_exists($class)) {
						return self::checkController($class, $method, $match['parameters']);
					}

					return false;
				}
			}
		}

		$path = explodeTrim('/', $path);

		if ($path) {
			$class = $namespace.camelCase($path[0], true);

			if (class_exists($class)) {
				array_shift($path);
				$method = $path ? camelCase(array_shift($path)) : 'index';

				return self::checkController($class, $method, $path);
			}

			if (class_exists($namespace.$config['default'])) {
				$method = camelCase(array_shift($path));

				return self::checkController($namespace.$config['default'], $method, $path);
			}

			return false;
		}

		if (class_exists($namespace.$config['default'])) {
			return self::checkController($namespace.$config['default'], 'index');
		}

		return false;
	}



	/**
	 * static function match (string $path, array $route)
	 *
	 * Returns boolean
	 */
	static private function match ($path, $route) {
		if (strpos($route['pattern'], '(') === false) {
			if (preg_match('|^'.preg_quote($route['pattern'], '|').'$|', $path, $matches)) {
				return array(
					'routing' => $matches[0],
					'parameters' => (array)$route['defaults']
				);
			}

			return false;
		}

		if ($route['defaults']) {
			$route['pattern'] = preg_replace('#(\('.implode('|', array_keys($route['defaults'])).'(\s+[^\)]+)?\)[^?])#', '\\1?', $route['pattern']);
		}

		$route['pattern'] = preg_replace_callback('#/\((\w+)(\s+[^\)]+)?\)\??#', array(self, 'matchCallback'), $route['pattern']);

		if (preg_match('|^'.$route['pattern'].'$|', $path, $matches)) {
			$return = array(
				'routing' => array_shift($matches),
				'parameters' => (array)$route['defaults'],
			);

			foreach ($matches as $name => $value) {
				if (is_string($name)) {
					$return['parameters'][$name] = $value;
					next($matches);
				}
			}

			return $return;
		}

		return false;
	}



	/**
	 * static function matchCallback (array $matches)
	 *
	 * Returns string
	 */
	static private function matchCallback ($matches) {
		if (!$matches[2]) {
			$matches[2] = '[^/]+';
		}

		if (substr($matches[0], -1) === '?') {
			return '/?(?P<'.$matches[1].'>'.trim($matches[2]).')?';
		}

		return '/(?P<'.$matches[1].'>'.trim($matches[2]).')';
	}



	/**
	 * static function checkController (string $class, string $method, [array $parameters])
	 *
	 * Returns boolean
	 */
	static private function checkController ($class, $method, array $parameters = array()) {
		$Class = new \ReflectionClass($class);

		if ($Class->isInstantiable() && $Class->hasMethod($method)) {
			$Method = $Class->getMethod($method);

			if ($Method->isPublic() && !$Method->isStatic()) {
				$all_params = $params = array();

				//knatsort of parameters
				if ($parameters) {
					$tmp_parameters = $parameters;
					$keys = array_keys($parameters);
					$parameters = array();

					natsort($keys);
					foreach ($keys as $key) {
						$parameters[$key] = $tmp_parameters[$key];
					}
				}

				foreach ($Method->getParameters() as $Parameter) {
					$name = $Parameter->getName();

					if ($parameters[$name]) {
						$all_params[$name] = $params[$name] = $parameters[$name];
					} else if ($parameters[0]) {
						$all_params[$name] = $params[$name] = array_shift($parameters);
					} else if ($Parameter->isOptional()) {
						$all_params[$name] = $Parameter->getDefaultValue();
					} else {
						return false;
					}
				}

				return array($Class->getName(), $Method->getName(), $all_params, array_merge($parameters, $params));
			}
		}

		return false;
	}
}
?>