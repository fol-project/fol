<?php
/**
 * Fol\App
 * 
 * This is the abstract class that all apps must extend. Provides the basic functionality parameters (paths, urls, namespace, parent, etc)
 */

namespace Fol;

use Fol\Http\Router;
use Fol\Http\Route;
use Fol\Http\Request;
use Fol\Http\HttpException;

abstract class App {
	public $parent;
	private $services;


	/**
	 * Magic function to get some special properties.
	 * Instead calculate this on the __constructor, is better use __get to do not obligate to call this constructor in the extensions of this class
	 * 
	 * @param string $name The name of the property
	 * 
	 * @return string The property value or null
	 */
	public function __get ($name) {
		//Registered services
		if (isset($this->services[$name])) {
			return $this->$name = $this->services[$name]();
		}

		//The router
		if ($name === 'router') {
			return $this->router = new Router($this->url);
		}

		//The request
		if ($name === 'request') {
			return $this->request = (php_sapi_name() === 'cli') ? Request::createFromCli($argv) : Request::createFromGlobals();
		}

		//The app name. (Web)
		if ($name === 'name') {
			return $this->name = substr(strrchr($this->namespace, '\\'), 1);
		}

		//The app namespace. (Apps\Web)
		if ($name === 'namespace') {
			return $this->namespace = (new \ReflectionClass($this))->getNameSpaceName();
		}

		//The app path. (/sites/my-site/web)
		if ($name === 'path') {
			return $this->path = str_replace('\\', '/', dirname((new \ReflectionClass($this))->getFileName()));
		}

		//The app base url
		if ($name === 'url') {
			return $this->url = '';
		}

		//The assets app path. (/sites/my-site/web/assets)
		if ($name === 'assetsPath') {
			return $this->assetsPath = $this->path.'/assets';
		}

		//The assets app url (/web/assets)
		if ($name === 'assetsUrl') {
			return $this->assetsUrl = BASE_URL.preg_replace('|^'.BASE_PATH.'|', '', $this->path).'/assets';
		}
	}


	/**
	 * Define a Parent property (the app that contain this app)
	 * 
	 * @param Fol\App $Parent An App instance
	 */
	public function setParent (App $parent) {
		$this->parent = $parent;
	}


	/**
	 * Returns the name of any class with the same namespace of the app
	 * 
	 * @example $app->getClass('Models', 'Posts'); //Returns Apps\Web\Models\Posts
	 * 
	 * @return string
	 */
	public function getClass ($class) {
		return $this->namespace.'\\'.implode('\\', func_get_args());
	}


	/**
	 * Register a new service
	 * 
	 * @param string $name The service name
	 * @param Closure $resolver A function that returns a service instance
	 */
	public function register ($name, \Closure $resolver = null) {
		if (is_array($name)) {
			foreach ($name as $name => $resolver) {
				$this->register($name, $resolver);
			}

			return;
		}

		$this->services[$name] = $resolver;
	}


	/**
	 * Deletes a service
	 * 
	 * @param string $name The service name
	 */
	public function unregister ($name) {
		unset($this->services[$name]);
	}


	/**
	 * Handle a request and returns a response
	 * 
	 * @param  Fol\Http\Request $request The request to handle
	 * @param  string $name Set a route name to force to use this route instead detect by url
	 * 
	 * @return Fol\Http\Response The resulted response
	 */
	public function handleRequest (Request $request = null, $name = null) {
		if ($request === null) {
			$request = $this->request;
		}

		try {
			$route = ($name === null) ? $this->router->match($request) : $this->router->getByName($name);

			if ($route) {
				try {
					$request->parameters->set($route->getParameters());
					$response = $request->generateResponse();

					$resp = $this->executeRoute($route, [$request, $response]);

					if ($resp instanceof Response) {
						$response = $resp;
					} else if (is_string($resp)) {
						$response->appendContent($resp);
					}
				} catch (\Exception $exception) {
					if ($exception instanceof HttpException) {
						throw $exception;
					} else {
						throw new HttpException('Error Processing Request', 500, $exception);
					}
				}
			} else {
				throw new HttpException('This route does not exits', 404);
			}
		} catch (HttpException $exception) {
			if (!isset($response)) {
				$response = $request->generateResponse();
			}

			$response->setStatus($exception->getCode());

			if (($route = $this->router->getByName('error'))) {
				$request->parameters->set('exception', $exception);

				return $this->handleRequest($request, 'error');
			}

			$response = $request->generateResponse();
			$response->setStatus($exception->getCode());
			$response->setContent($exception->getMessage());

			return $response;
		}

		return $response;
	}


	/**
	 * Executes a route target
	 * 
	 * @param Fol\Http\Route $route The route to execute
	 * @param array $arguments The arguments passed to the controller
	 * 
	 * @return string The return of the controller
	 */
	protected function executeRoute (Route $route, array $arguments = array(), array $constructor_arguments = array()) {
		ob_start();

		$target = $route->getTarget();

		if (is_callable($target)) {
			$return = call_user_func_array($target, $arguments);
		} elseif (is_string($target)) {
			if (strpos($target, '::') !== false) {
				list($class, $method) = explode('::', $target, 2);

				$class = new \ReflectionClass($this->namespace.'\\Controllers\\'.$class);
				$controller = $class->newInstanceWithoutConstructor();
				$controller->App = $this;

				if (($Constructor = $class->getConstructor())) {
					$Constructor->invokeArgs($controller, $constructor_arguments);
				}

				$return = $class->getMethod($method)->invokeArgs($controller, $arguments);
			} else {
				$return = call_user_func_array([$this, $target], $arguments);
			}
		}

		return ob_get_clean().$return;
	}
}
