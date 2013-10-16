<?php
/**
 * Fol\Router\Route
 * 
 * Class to manage a route
 * Based in PHP-Router library (https://github.com/dannyvankooten/PHP-Router) and Aura-PHP.Router (https://github.com/auraphp/Aura.Router)
 */
namespace Fol\Router;

use Fol\Http\Request;
use Fol\Http\Response;
use Fol\Http\HttpException;

class Route {
	private $name;
	private $path;
	private $method;
	private $filters;
	private $parameters = array();
	private $secure;
	
	private $match;
	private $matches;
	private $generate;
	private $regex = null;

	private $target;
	private $wildcard;

	public function __construct ($name, array $config = array()) {
		$this->name = $name;

		$this->path = $config['path'];
		$this->target = $config['target'];

		if (isset($config['method'])) {
			$this->method = (array) $config['method'];
		}

		if (isset($config['filters'])) {
			$this->filters = (array) $config['filters'];
		}

		if (isset($config['parameters'])) {
			$this->parameters = (array) $config['parameters'];
		}

		if (isset($config['secure'])) {
			$this->secure = (bool) $config['secure'];
		}

		$this->setRegex();
	}

	public function getType () {
		return 'url';
	}

	public function getName () {
		return $this->name;
	}

	public function getPath () {
		return $this->path;
	}

	public function getTarget () {
		return $this->target;
	}

	private function setRegex () {
		if (substr($this->path, -2) === '/*') {
			$this->path = substr($this->path, 0, -2).'/{:__wildcard__:(.*)}';
			$this->wildcard = '__wildcard__';
		}

		if (preg_match('/\/\{:([\w-]+)([\+\*])\}$/i', $this->path, $matches)) {
			$this->wildcard = $matches[1];
			$pos = strrpos($this->path, $matches[0]);

			if ($matches[2] === '*') {
				$this->path = substr($this->path, 0, $pos)."(/{:{$this->wildcard}:(.*)})?";
			} else {
				$this->path = substr($this->path, 0, $pos)."/{:{$this->wildcard}:(.+)}";
			}
		}

		preg_match_all("/\{:(.*?)(:(.*?))?\}/", $this->path, $matches, PREG_SET_ORDER);

		foreach ($matches as $match) {
			$whole = $match[0];
			$name = $match[1];

			if (isset($match[3])) {
				$this->filters[$name] = ($match[3] === '?') ? '([^/]+)?' : $match[3];
				$this->path = str_replace($whole, "{:$name}", $this->path);
			} elseif (!isset($this->filters[$name])) {
				$this->filters[$name] = '([^/]+)';
			}
		}

		$this->regex = $this->path;

		if ($this->filters) {
			$keys = $vals = [];

			foreach ($this->filters as $name => $filter) {
				if ($filter[0] !== '(') {
					throw new \Exception("Filter for parameter '$name' must start with '('.");
				} else if (substr($filter, -1) === '?') {
					$keys[] = "/{:$name}";
					$vals[] = "(/(?P<$name>".substr($filter, 1, -1).')?';
				} else {
					$keys[] = "{:$name}";
					$vals[] = "(?P<$name>".substr($filter, 1);
				}

				if (!isset($this->parameters[$name])) {
					$this->parameters[$name] = null;
				}
			}

			$this->regex = str_replace($keys, $vals, $this->regex);
		}

		$this->regex = "#^{$this->regex}$#";
	}


	public function checkMethod ($request) {
		if (!$this->method || in_array($request->getMethod(), $this->method)) {
			return true;
		}

		return false;
	}

	public function checkSecure ($request) {
		if ($this->secure === null) {
			return true;
		}

		$secure = (($request->getScheme() === 'https') || ($request->getPort() === 443));

		return ($this->secure === $secure);
	}

	public function checkRegex ($request) {
		return preg_match($this->regex, $request->getPath(), $this->matches);
	}


	/**
	 * Reverse the route
	 * 
	 * @param array $parameters Optional array of parameters to use in URL
	 * 
	 * @return string The url to the route
	 */
	public function generate (array $parameters = array()) {
		$replace = [];

		foreach ($this->parameters as $name => $value) {
			if (isset($parameters[$name])) {
				$replace["{:$name}"] = rawurlencode($parameters[$name]);
				unset($parameters[$name]);
			} else if ($value !== null) {
				$replace["{:$name}"] = rawurlencode($value);
			} else {
				$replace["/{:$name}"] = '';
			}
		}

		$path = strtr($this->path, $replace);

		if ($parameters) {
			return "$path?".http_build_query($parameters);
		}

		return $path;
	}


	public function match ($request) {
		if (!($this->checkMethod($request) && $this->checkSecure($request) && $this->checkRegex($request))) {
			return false;
		}

		foreach ($this->matches as $key => $value) {
			if (is_string($key)) {
				$this->parameters[$key] = rawurldecode($value);
			}
		}

		if ($this->wildcard) {
			if (empty($this->parameters[$this->wildcard])) {
				$this->parameters[$this->wildcard] = [];
			} else {
				$this->parameters[$this->wildcard] = array_map('rawurldecode', explode('/', $this->parameters[$this->wildcard]));
			}

			if ($this->wildcard === '__wildcard__') {
				$this->parameters['*'] = $this->parameters['__wildcard__'];
				unset($this->parameters['__wildcard__']);
			}
		}
		
		return true;
	}


	public function execute ($app, $request) {
		ob_start();

		$return = '';
		$response = $request->generateResponse();

		if ($this->parameters) {
			$request->parameters->set($this->parameters);
		}

		try {
			list($class, $method) = $this->target;

			$class = new \ReflectionClass($class);
			$controller = $class->newInstanceWithoutConstructor();
			$controller->app = $app;
			$controller->route = $this;

			if (($constructor = $class->getConstructor())) {
				$constructor->invoke($controller, $request, $response);
			}

			if ($method) {
				$return = $class->getMethod($method)->invoke($controller, $request, $response);
			} else {
				$return = $controller($request, $response);
			}

			unset($controller);
		} catch (\Exception $exception) {
			ob_clean();

			if (!($exception instanceof HttpException)) {
				$exception = new HttpException('Error processing request', 500, $exception);
			}

			throw $exception;
		}

		if ($return instanceof Response) {
			$return->appendContent(ob_get_clean());
			
			return $return;
		}

		$response->appendContent(ob_get_clean().$return);

		return $response;
	}
}
