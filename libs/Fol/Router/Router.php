<?php
/**
 * Fol\Router\Router
 * 
 * Class to manage all routes
 * Based in PHP-Router library (https://github.com/dannyvankooten/PHP-Router) and Aura-PHP.Router (https://github.com/auraphp/Aura.Router)
 */
namespace Fol\Router;

use Fol\Http\Response;
use Fol\Http\HttpException;
use Fol\App;

class Router {
	private $routes = array();
	private $fileRoutes = array();
	private $errorController;
	private $routeFactory;
	private $absoluteUrl;


	/**
	 * Constructor function. Defines the base url
	 * 
	 * @param string $baseUrl
	 */
	public function __construct (RouteFactory $routeFactory) {
		$this->routeFactory = $routeFactory;
		$this->absoluteUrl = BASE_ABSOLUTE_URL;
	}


	/**
	* Route factory method
	*
	* Maps the given URL to the given target.
	* @param string $name string The route name.
	* @param string $url string
	* @param mixed $target The target of this route.
	* @param array $config Array of optional arguments.
	*/
	public function map ($name, $url, $target = '', array $config = array()) {
		if ($name === null) {
			$this->routes[] = $this->routeFactory->createRoute($name, $url, $target, $config);
		} else {
			$this->routes[$name] = $this->routeFactory->createRoute($name, $url, $target, $config);
		}
	}


	/**
	* FileRoute factory method
	*
	* Maps the given URL to the given target.
	* @param string $path string
	* @param mixed $target The target of this route
	*/
	public function mapFile ($path, $target = '') {
		$this->fileRoutes[] = $this->routeFactory->createFileRoute($path, $target);
	}


	public function setError ($target) {
		$this->errorController = $this->routeFactory->createErrorRoute($target);
	}


	/**
	 * Match given request url and request method and see if a route has been defined for it
	 * If so, return route's target
	 * If called multiple times
	 */
	public function match ($request) {
		foreach ($this->fileRoutes as $route) {
			if ($route->match($request)) {
				return $route;
			}
		}

		foreach ($this->routes as $route) {
			if ($route->match($request)) {
				return $route;
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

		$route = $this->routes[$name];

		if ($absolute === true) {
			return $this->absoluteUrl.BASE_URL.$route->generate($params);
		}

		return BASE_URL.$route->generate($params);
	}


	public function handle ($request, App $app) {
		if (($route = $this->match($request))) {
			try {
				$response = $route->execute($app, $request);
			} catch (HttpException $exception) {
				return $this->errorController->execute($app, $exception, $request);
			}

			return $response;
		}

		return $this->errorController->execute($app, new HttpException('Not found', 404), $request);
	}
}
