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
	private $parameters;
	private $secure;
	
	private $match;
	private $matches;
	private $generate;
	private $regex = null;

	private $target;
	private $wildcard;

	public function __construct ($name, $path, $target, array $config = array()) {
		$this->name = $name;
		$this->path = $path;
		$this->target = $target;

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

	private function setRegex () {
		if (substr($this->path, -2) === '/*') {
			$this->path = substr($this->path, 0, -2).'/{:__wildcard__:(.*)}';
			$this->wildcard = '__wildcard__';
		}

		if (preg_match('/\/\{:([\w-]+)([\+\*])\}$/i', $this->path, $matches)) {
			$this->wildcard = $matches[1];
			$pos = strrpos($this->path, $matches[0]);
			$this->path = substr($this->path, 0, $pos)."/{:{$this->wildcard}:(.".$matches[2].")}";
		}

		preg_match_all("/\{:(.*?)(:(.*?))?\}/", $this->path, $matches, PREG_SET_ORDER);

		foreach ($matches as $match) {
			$whole = $match[0];
			$name = $match[1];

			if (isset($match[3])) {
				$this->filters[$name] = $match[3];
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
				} else {
					$keys[] = "{:$name}";
					$vals[] = "(?P<$name>" . substr($filter, 1);
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
		$parameters = array_merge($this->parameters, $parameters);

		foreach ($parameters as $key => $val) {
			$replace["{:$key}"] = rawurlencode($val);
		}

		return BASE_URL.strtr($this->path, $replace);
	}


	public function match ($request) {
		$match = $this->checkRegex($request)
			  && $this->checkMethod($request)
			  && $this->checkSecure($request);

		if (!$match) {
			return false;
		}

		foreach ($this->matches as $key => $value) {
			if (is_string($key)) {
				$this->parameters[$key] = rawurldecode($value);
			}
		}

		if ($this->wildcard) {
			if (empty($this->parameters[$this->wildcard])) {
				$this->values[$this->wildcard] = [];
			} else {
				$this->values[$this->wildcard] = array_map('rawurldecode', explode('/', $this->values[$this->wildcard]));
			}

			if ($this->wildcard === '__wildcard__') {
				$this->values['*'] = $this->values['__wildcard__'];
				unset($this->values['__wildcard__']);
			}
		}
		
		return true;
	}


	public function execute ($app, $request) {
		ob_start();

		$return = '';
		$response = $request->generateResponse();

		try {
			list($class, $method) = $this->target;

			$class = new \ReflectionClass($class);
			$controller = $class->newInstanceWithoutConstructor();
			$controller->app = $app;

			if (($constructor = $class->getConstructor())) {
				$constructor->invoke($controller);
			}

			$return = $class->getMethod($method)->invoke($controller, $this, $request, $response);
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
