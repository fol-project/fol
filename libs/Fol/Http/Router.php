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
	private $namedRoutes = array();
	private $baseUrl = '';


	/**
	 * Constructor function. Defines the base url
	 * 
	 * @param string $baseUrl
	 */
	public function __construct ($baseUrl = '') {
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
	* @param string $routeUrl string
	* @param mixed $target The target of this route. Can be anything. You'll have to provide your own method to turn *      this into a filename, controller / action pair, etc..
	* @param array $config Array of optional arguments.
	*/
	public function map ($routeUrl, $target = '', array $config = array()) {
		$Route = new Route($this->baseUrl.$routeUrl, $target, $config);

		if (($name = $Route->getName()) && !isset($this->namedRoutes[$name])) {
			$this->namedRoutes[$name] = $Route;
		}

		$this->routes[] = $Route;
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
	 * Reverse route a named route
	 * 
	 * @param string $routeName The name of the route to reverse route.
	 * @param array $params Optional array of parameters to use in URL
	 * 
	 * @return string The url to the route
	 */
	public function generate ($routeName, array $params = array()) {
		if (!isset($this->namedRoutes[$routeName])) {
			throw new Exception("No route with the name $routeName has been found.");
		}

		return $this->namedRoutes[$routeName]->generate($params);
	}


	/**
	 * Handle a http request: search a controller and execute it
	 * 
	 * @param Fol\App $App The instance of the application
	 * @param Fol\Http\Request $Request The request object
	 * 
	 * @return Fol\Http\Response The response object with the controller result
	 */
	public function handle (\Fol\App $App, Request $Request) {
		try {
			if (($Route = $this->match($Request)) === false) {
				throw new HttpException(Headers::$status[404], 404);
			} else {
				$Response = $Route->execute($App, $Request);
			}
		} catch (\Exception $Exception) {
			if (($Route = $this->namedRoutes['error']) === false) {
				$Response = new Response($Exception->getMessage(), $Exception->getCode() ?: null);
			} else {
				$Response = $Route->execute($App, $Request, [$Exception]);
			}
		}

		return $Response;
	}
}