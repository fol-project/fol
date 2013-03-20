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
		$Route = new Route($name, $this->baseUrl.$url, $target, $config);

		if ($name === null) {
			$this->routes[] = $Route;
		} else {
			$this->routes[$name] = $Route;
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
	 * Reverse route a named route
	 * 
	 * @param string $routeName The name of the route to reverse route.
	 * @param array $params Optional array of parameters to use in URL
	 * @param boolean $absolute Set true to generate absolute urls
	 * 
	 * @return string The url to the route
	 */
	public function generate ($routeName, array $params = array(), $absolute = false) {
		if (!isset($this->routes[$routeName])) {
			throw new Exception("No route with the name $routeName has been found.");
		}

		if ($absolute === true) {
			return $this->absoluteUrl.$this->routes[$routeName]->generate($params, $absolute);
		}

		return $this->routes[$routeName]->generate($params, $absolute);
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
				$Route->execute($App, $Request);
			}
		} catch (HttpException $Exception) {
			if (!isset($this->routes['error'])) {
				$Request->Response->setContent($Exception->getMessage());
				$Request->Response->setStatus($Exception->getCode());
			} else {
				$this->routes['error']->execute($App, $Request, [$Exception]);
			}
		} catch (\Exception $Exception) {
			if (!isset($this->routes['error'])) {
				$Request->Response->setContent($Exception->getMessage());
				$Request->Response->setStatus(500);
			} else {
				$this->routes['error']->execute($App, $Request, [$Exception]);
			}
		}
	}
}