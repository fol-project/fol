<?php
namespace Fol;

class Router {
	public $Controller;
	public $controller;
	public $method;
	public $params;
	public $domain;
	public $subdomains = array();
	public $path;
	public $scene;



	/**
	 * public function __construct (void)
	 *
	 * Detects request info
	 * Returns none
	 */
	public function __construct () {

		//Domain and subdomains
		$server = array_reverse(explode('.', getenv('SERVER_NAME')));

		if ($server[0] === 'localhost') {
			$this->domain = array_shift($server);
			$this->subdomains = $server;
		} else if ($domain[1] === 'co' && strlen($domain[0]) === 2) {
			$this->domain = $server[2].'.'.$server[1].'.'.$server[0];
			$this->subdomains = array_slice($server, 3);
		} else {
			$this->domain = $server[1].'.'.$server[0];
			$this->subdomains = array_slice($server, 2);
		}

		//Path
		$path = preg_replace('|^'.preg_quote(BASE_HTTP).'|', '', getenv('REQUEST_URI'));
		$path = str_replace('$', '', parse_url($path, PHP_URL_PATH));
		$this->path = explodeTrim('/', urldecode($path));

		//Detect scene
		if (($this->scene = $this->detectScene())) {
			global $Config;

			$config = $Config->get('scenes');

			define('SCENE_PATH', BASE_PATH.$config[$this->scene]['folder'].'/');
			define('SCENE_HTTP', BASE_HTTP.(($config[$this->scene]['detection'] === 'subfolder') ? $this->scene.'/' : ''));
			define('SCENE_REAL_HTTP', BASE_HTTP.$config[$this->scene]['folder'].'/');
		}
	}



	/**
	 * private function detectScene (void)
	 *
	 * Detects the current scene
	 * Returns string/false
	 */
	private function detectScene () {
		global $Config;

		$config = $Config->get('scenes');

		//Detect subdomain
		if ($this->subdomains && $config[strtolower($this->subdomains[0])]['detection'] === 'subdomain') {
			return strtolower(array_shift($this->subdomains));
		}

		//Detect subfolder
		if ($this->path && $config[strtolower($this->path[0])]['detection'] === 'subfolder') {
			return strtolower(array_shift($this->path));
		}

		//Get first scene by default
		if ($config) {
			reset($config);
			return key($config);
		}

		return false;
	}



	/**
	 * public function go ([string $path])
	 *
	 * Check the route and execute the controller. Returns the value returned by the controller
	 * Returns mixed
	 */
	public function go ($path = null) {
		if ($path) {
			$this->path = explodeTrim('/', $path);
		}

		list($class, $method, $parameters) = $this->getController($this->path);

		try {
			if ($class) {
				$this->Controller = new $class;
				$this->controller = $class;
				$this->method = $method;
				$this->parameters = $parameters;
				$result = call_user_func_array(array($this->Controller, $method), $parameters);
			} else {
				$this->Controller = null;
				exception('Controller not found', 404);
			}
		} catch (\Fol\Exception $e) {
			$e->runController();
		}

		return $result;
	}



	/**
	 * private function getController (array $path)
	 *
	 * Check the route and returns the controller
	 * Returns array/false
	 */
	private function getController ($path) {
		global $Config;

		$config = $Config->get('controller');

		//Get controller by routings
		if ($config['routing']) {
			$path_route = '/'.implode('/', $path);

			foreach ($config['routing'] as $route => $route_config) {
				if (is_string($route_config)) {
					$route_config = array('controller' => $route_config);
				}

				if ($route = $this->matchRoute($path_route, $route, $route_config['defaults'])) {
					list($Class, $Method) = explodeTrim(':', $route_config['controller']);

					$Class = 'Controllers\\'.camelCase($Class, true);

					if ($Method && class_exists($Class)) {
						$Class = new \ReflectionClass($Class);

						if ($Class->isInstantiable() && $Class->hasMethod($Method)) {
							$Method = $Class->getMethod($Method);

							if ($Method->isPublic() && !$Method->isStatic()) {
								$params = array();

								foreach ($Method->getParameters() as $Parameter) {
									$name = $Parameter->getName();

									if ($route['parameters'][$name]) {
										$params[] = $route['parameters'][$name];
									} else if (isset($route_config['defaults'][$name])) {
										$params[] = $route_config['defaults'][$name];
									} else if ($Parameter->isOptional()) {
										$params[] = $Parameter->getDefaultValue();
									} else {
										return false;
									}
								}

								$this->path = $path;

								return array($Class->getName(), $Method->getName(), $params);
							}
						}
					}

					return false;
				}
			}
		}

		//Get controller by path
		if ($path) {
			$Class = 'Controllers\\'.camelCase($path[0], true);

			if (class_exists($Class)) {
				array_shift($path);
		
				$Class = new \ReflectionClass($Class);
				$Method = $path ? camelCase(array_shift($path)) : 'index';
			} else {
				$Class = new \ReflectionClass('Controllers\\'.$config['default']);
				$Method = camelCase(array_shift($path));
			}
		} else {
			$Class = 'Controllers\\'.$config['default'];

			if (class_exists($Class)) {
				$Class = new \ReflectionClass($Class);
				$Method = 'index';
			} else {
				return false;
			}
		}

		if ($Class->isInstantiable() && $Class->hasMethod($Method)) {
			$Method = $Class->getMethod($Method);

			if ($Method->isPublic() && !$Method->isStatic() && ($Method->getNumberOfRequiredParameters() <= count($path))) {
				$params = array();
				$this->path = $path;

				foreach ($Method->getParameters() as $Parameter) {
					$name = $Parameter->getName();

					if ($path) {
						$params[] = array_shift($path);
					} else if ($Parameter->isOptional()) {
						$params[] = $Parameter->getDefaultValue();
					} else {
						return false;
					}
				}

				return array($Class->getName(), $Method->getName(), $params);
			}
		}

		return false;
	}



	/**
	 * private function matchRoute (string $original, string $route, array $defaults)
	 *
	 * Check the route using a regular expression
	 * Returns array/false
	 */
	private function matchRoute ($original, $route, $defaults) {
		if (strpos($route, '(') === false) {
			if (preg_match('|^'.$route.'$|', $original, $matches)) {
				return array(
					'regexp' => $route,
					'route' => $matches[0],
					'parameters' => array()
				);
			}

			return false;
		}

		if ($defaults) {
			$route = preg_replace('#(\('.implode('|', array_keys($defaults)).'(\s+[^\)]+)?\)[^?])#', '\\1?', $route);
		}

		$route = preg_replace_callback('#/\((\w+)(\s+[^\)]+)?\)\??#', function ($matches) {
			if (!$matches[2]) {
				$matches[2] = '[^/]+';
			}

			if (substr($matches[0], -1) === '?') {
				return '/?(?P<'.$matches[1].'>'.trim($matches[2]).')?';
			}

			return '/(?P<'.$matches[1].'>'.trim($matches[2]).')';
		}, $route);

		if (preg_match('|^'.$route.'$|', $original, $matches)) {
			return array(
				'regexp' => $route,
				'route' => array_shift($matches),
				'parameters' => $matches
			);
		}

		return false;
	}
}
?>