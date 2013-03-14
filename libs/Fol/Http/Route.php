<?php
/**
 * Fol\Http\Route
 * 
 * Class to manage a route
 * Based in PHP-Router library (https://github.com/dannyvankooten/PHP-Router)
 */
namespace Fol\Http;

class Route {
	private $url;
	private $methods = array('GET','POST','PUT','DELETE');
	private $target;
	private $name;
	private $filters = array();
	private $parameters = array();
	private $regex = null;

	public function __construct ($name, $url, $target, array $config = array()) {
		$this->name = $name;
		$this->url = $url;
		$this->target = $target;

		if (isset($config['methods'])) {
			$this->methods = (array)$config['methods'];
		}

		if (isset($config['filters'])) {
			$this->filters = $config['filters'];
		}

		if (isset($config['parameters'])) {
			$this->parameters = (array)$config['parameters'];
		}
	}

	public function getUrl () {
		return $this->url;
	}

	public function getTarget () {
		return $this->target;
	}

	public function getMethods () {
		return $this->methods;
	}

	public function getName () {
		return $this->name;
	}

	public function getParameters () {
		return $this->parameters;
	}

	public function getRegex () {
		if ($this->regex !== null) {
			return $this->regex;
		}

		$filters = $this->filters;

		$regex = preg_replace_callback('/:([\w-]+)/', function ($matches) use ($filters) {
			if (isset($matches[1]) && isset($filters[$matches[1]])) {
				return $filters[$matches[1]];
			}

			return '([^\/]+)';
		}, $this->url);

		return $this->regex = "@^$regex\$@i";
	}

	
	/**
	 * Check if the Request match with the route
	 *
	 * @param Fol\Http\Request $Request The request object
	 *
	 * @return boolean True if it match, false if not
	 */
	public function match ($Request) {
		if (!in_array($Request->getMethod(), $this->methods)) {
			return false;
		}

		if (!preg_match($this->getRegex(), $Request->getPath(), $matches)) {
			return false;
		}

		$params = array();

		if (preg_match_all("/:([\w-]+)/", $this->url, $argument_keys)) {
			$argument_keys = $argument_keys[1];

			foreach ($argument_keys as $key => $name) {
				if (isset($matches[$key + 1])) {
					$params[$name] = $matches[$key + 1];
				}
			}
		}

		$this->parameters = array_replace($this->parameters, $params);

		return true;
	}


	/**
	 * Reverse the route
	 * 
	 * @param array $params Optional array of parameters to use in URL
	 * 
	 * @return string The url to the route
	 */
	public function generate (array $params = array()) {
		$url = $this->url;

		if ($params && preg_match_all("/:(\w+)/", $url, $param_keys)) {
			$param_keys = $param_keys[1];

			foreach ($param_keys as $i => $key) {
				if (isset($params[$key])) {
					$url = preg_replace("/:(\w+)/", $params[$key], $url, 1);
				}
			}
		}

		return BASE_URL.$url;
	}


	/**
	 * Executes the route target
	 * 
	 * @param Fol\App $App The app object
	 * @param Fol\Http\Request $Request The request used
	 * @param array $extra_arguments Extra arguments passed to the controller (after $Request and $App)
	 * 
	 * @return Fol\Http\Response The response of the controller
	 */
	public function execute (\Fol\App $App, Request $Request, array $extra_arguments = array()) {
		ob_start();

		$arguments = $extra_arguments;
		$target = $this->target;

		$Request->Parameters->set($this->parameters);
		$Response = null;

		if (is_callable($target)) {
			array_unshift($arguments, $Request, $App);

			$Response = call_user_func_array($target, $arguments);
		} elseif (is_string($target) && (strpos($target, '::') !== false)) {
			list($class, $method) = explode('::', $target, 2);
			array_unshift($arguments, $Request);

			$class = $App->namespace.'\\Controllers\\'.$class;

			$Class = new $class($App, $Request);

			$Response = call_user_func_array([$Class, $method], $arguments);
		}

		if (!($Response instanceof Response)) {
			$Response = new Response($Response);
		}

		$Response->prependContent(ob_get_clean());

		return $Response;
	}
}