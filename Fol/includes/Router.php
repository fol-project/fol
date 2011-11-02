<?php
namespace Fol;

class Router {
	public $domain;
	public $subdomains = array();
	public $path;
	public $scene;
	public $module;
	public $exit_mode;



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
		$path = preg_replace('|^'.preg_quote(BASE_WWW).'|', '', getenv('REQUEST_URI'));
		$path = str_replace('$', '', parse_url($path, PHP_URL_PATH));
		$this->path = explodeTrim('/', urldecode($path));

		//Detect scene/module/exit_mode
		if (($this->scene = $this->detectScene())) {
			global $Config;

			$config = $Config->get('scene', 'scene', $this->scene);

			$this->module = $this->detectModule($config);
			$this->exit_mode = $this->detectExitMode($config);
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

		$config = $Config->get('scenes', 'base');

		//Detect subdomain
		if ($this->subdomains && $config[strtolower($this->subdomains[0])]['detection'] == 'subdomain') {
			return strtolower(array_shift($this->subdomains));
		}

		//Detect subfolder
		if ($this->path && $config[strtolower($this->path[0])]['detection'] == 'subfolder') {
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
	 * private function detectModule (array $config)
	 *
	 * Detects the current module
	 * Return string/false
	 */
	private function detectModule ($config) {
		$config = $config['modules'];

		//Detect subdomain
		if ($this->subdomains && ($config['detection'] == 'subdomain') && in_array(strtolower($this->subdomains[0]), $config['detection'])) {
			return strtolower(array_shift($this->subdomains));
		}

		//Detect subfolder
		if ($this->path && ($config['detection'] === 'subfolder')) {
			if (strtolower($this->path[0]) !== $config['subfolder']) {
				return false;
			}

			array_shift($this->path);

			if (in_array(strtolower($this->path[0]), $config['availables'])) {
				return strtolower(array_shift($this->path));
			}

			//Get first by default
			if ($config['availables']) {
				reset($config['availables']);
				return key($config['availables']);
			}
		}

		return false;
	}



	/**
	 * private function detectExitMode (array $config)
	 *
	 * Detects the current module
	 * Return string/false
	 */
	private function detectExitMode ($config) {
		$config = $config['exit_modes'];

		if ($config['detection'] == 'subdomain') {
			if ($this->subdomains && in_array(strtolower($this->subdomains[0]), $config['detection'])) {
				return strtolower(array_shift($this->config));
			}
		} else if ($config['detection'] === 'subfolder') {
			if ($this->path && in_array(strtolower($this->path[0]), $config['availables'])) {
				return strtolower(array_shift($this->path));
			}
		}

		//Get first by default
		if ($config['availables']) {
			reset($config['availables']);
			return key($config['availables']);
		}

		return false;
	}



	/**
	 * public function go ([string $path])
	 *
	 * Check the route and execute the controller
	 * Returns boolean
	 */
	public function go ($path = null) {
		if ($path) {
			$path = explodeTrim('/', $path);
		} else {
			$path = $this->path;
		}

		list($class, $method, $parameters) = $this->getController($path);

		try {
			if ($class) {
				$Controller = new $class;
				call_user_func_array(array($Controller, $method), $parameters);
			} else {
				throw new \Exception('Controller not found', 404);
			}
		} catch (Exception $e) {
			echo $e->getCode();
		}
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

		if ($config['routes']) {
			$path_route = '/'.implode('/', $path);

			foreach ($config['routes'] as $route => $route_config) {
				if ($parameters = $this->matchRoute($path_route, $route)) {
					if (is_string($route_config)) {
						$route_config = array('controller' => $route_config);
					}

					list($Class, $Method) = explodeTrim(':', $route_config['controller']);

					$Class = 'Controllers\\'.$this->camelCase($Class, true);

					if (!$Method && !class_exists($Class)) {
						return false;
					}

					$Class = new \ReflectionClass($Class);

					if ($route_config['defaults']) {
						$parameters['associative'] = array_merge((array)$parameters['associative'], $route_config['defaults']);
					}

					break;
				}
			}
		}

		if (!$Class) {
			if ($path) {
				$Class = 'Controllers\\'.$this->camelCase($path[0], true);

				if (class_exists($Class)) {
					array_shift($path);
					$Class = new \ReflectionClass($Class);

					$Method = $path ? $this->camelCase(array_shift($path)) : 'index';
				} else {
					$Class = new \ReflectionClass('Controllers\\'.$this->camelCase($config['default'], true));
					$Method = $this->camelCase(array_shift($path));
				}
			} else {
				$Class = 'Controllers\\'.$this->camelCase($config['default'], true);

				if (class_exists($Class)) {
					$Class = new \ReflectionClass($Class);
					$Method = 'index';
				}
			}

			$parameters = array('numerical' => $path);
		}

		if ($Class->isInstantiable() && $Class->hasMethod($Method)) {
			$Method = $Class->getMethod($Method);

			if ($Method->isPublic() && !$Method->isStatic() && ($Method->getNumberOfRequiredParameters() <= count($path))) {
				$params = array();

				foreach ($Method->getParameters() as $Parameter) {
					$name = $Parameter->getName();

					if (isset($parameters['associative'][$name])) {
						$params[] = $parameters['associative'][$name];
					} else if ($parameters['numerical']) {
						$params[] = array_shift($parameters['numerical']);
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
	 * private function matchRoute (string $original, $route)
	 *
	 */
	private function matchRoute ($original, $route) {
		if (strpos($route, '(') === false) {
			if (preg_match('|^'.$route.'$|', $original, $matches)) {
				return array(
					'regexp' => $route,
					'route' => $matches[0],
					'associative' => array(),
					'numerical' => array()
				);
			}

			return false;
		}

		$route = preg_replace_callback('|\((\w+)(\s+[^\)]+)?\)|', function ($matches) {
			if (!$matches[2]) {
				$matches[2] = '[^/]+';
			}

			return '(?P<'.$matches[1].'>'.trim($matches[2]).')';
		}, $route);

		if (preg_match('|^'.$route.'$|', $original, $matches)) {
			$variables = array();
			$next = true;

			foreach ($matches as $name => $value) {
				if ($next) {
					$next = false;
					continue;
				} else if (is_string($name)) {
					$variables['associative'][$name] = $value;
					$next = true;
				} else {
					$variables['numerical'][] = $value;
				}
			}

			return array(
				'regexp' => $route,
				'route' => $matches[0],
				'associative' => $variables['associative'],
				'numerical' => $variables['numerical']
			);
		}

		return false;
	}



	/**
	 * private function camelCase (string $string, [boolean $upper_first])
	 *
	 * Transform a string "my-string" to camelCase: "myString"
	 * Returns string
	 */
	private function camelCase ($string, $upper_first = false) {
		$string = str_replace('-', ' ', $string);
		$string = str_replace(' ', '', ucwords($string));

		if (!$upper_first) {
			return lcfirst($string);
		}

		return $string;
	}
}
?>