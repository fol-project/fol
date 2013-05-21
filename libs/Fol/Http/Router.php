<?php
/**
 * Fol\Http\Router
 * 
 * Class to manage all routes
 * Based in PHP-Router library (https://github.com/dannyvankooten/PHP-Router)
 */
namespace Fol\Http;

class Router {
	private $routes = array();
	private $baseUrl = '';
	private $absoluteUrl;


	/**
	 * Constructor function. Defines the base url
	 * 
	 * @param string $baseUrl
	 */
	public function __construct ($baseUrl = '') {
		$this->absoluteUrl = BASE_ABSOLUTE_URL;
		$this->setBaseUrl($baseUrl);
	}


	/**
	 * Set the base url
	 * 
	 * @param string $baseUrl 
	 */
	public function setBaseUrl ($baseUrl) {
		if (!empty($baseUrl) && (substr($baseUrl, -1) === '/')) {
			$this->baseUrl = substr($baseUrl, 0, -1);
		} else {
			$this->baseUrl = $baseUrl;
		}
	}


	/**
	* Route factory method
	*
	* Maps the given URL to the given target.
	* @param string $name string The route name.
	* @param string $url string
	* @param mixed $target The target of this route. Can be anything. You'll have to provide your own method to turn *      this into a filename, controller / action pair, etc..
	* @param array $config Array of optional arguments.
	*/
	public function map ($name, $url, $target = '', array $config = array()) {
		if ($name === null) {
			$this->routes[] = new Route($name, $url, $target, $config);
		} else {
			$this->routes[$name] = new Route($name, $url, $target, $config);
		}
	}


	/**
	 * Match given request url and request method and see if a route has been defined for it
	 * If so, return route's target
	 * If called multiple times
	 */
	public function match ($Request) {
		foreach ($this->routes as $Route) {
			if ($Route->match($Request)) {
				return $Route;
			}
		}

		return false;
	}


	/**
	 * Search a router by name
	 * 
	 * @param string $name The route name
	 * 
	 * @return Fol\Http\Route The route found or false
	 */
	public function getByName ($name) {
		if (!isset($this->routes[$name])) {
			return false;
		}

		return $this->routes[$name];
	}


	
	/**
	 * Reverse route a named route
	 * 
	 * @param string $name The name of the route to reverse route.
	 * @param array $params Optional array of parameters to use in URL
	 * @param boolean $absolute Set true to generate absolute urls
	 * 
	 * @return string The url to the route
	 */
	public function generate ($name, array $params = array(), $absolute = false) {
		if (!isset($this->routes[$name])) {
			throw new \Exception("No route with the name $name has been found.");
		}

		$Route = $this->routes[$name];

		if ($absolute === true) {
			return $this->absoluteUrl.$Route->generate($params, $absolute);
		}

		return $Route->generate($params, $absolute);
	}
}
