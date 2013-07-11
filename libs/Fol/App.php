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
	public $Parent;
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
	public function setParent (App $Parent) {
		$this->Parent = $Parent;
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
	 * @param  Fol\Http\Request $Request The request to handle
	 * @param  string $name Set a route name to force to use this route instead detect by url
	 * 
	 * @return Fol\Http\Response The resulted response
	 */
	public function handleRequest (Router $Router, Request $Request, $name = null) {
		try {
			$Route = ($name === null) ? $Router->match($Request) : $Router->getByName($name);

			if ($Route) {
				try {
					$Request->Parameters->set($Route->getParameters());
					$Response = $Request->generateResponse();

					$resp = $this->executeRoute($Route, [$Request, $Response]);

					if ($resp instanceof Response) {
						$Response = $resp;
					} else if (is_string($resp)) {
						$Response->appendContent($resp);
					}
				} catch (\Exception $Exception) {
					if ($Exception instanceof HttpException) {
						throw $Exception;
					} else {
						throw new HttpException('Error Processing Request', 500, $Exception);
					}
				}
			} else {
				throw new HttpException('This route does not exits', 404);
			}
		} catch (HttpException $Exception) {
			if (!isset($Response)) {
				$Response = $Request->generateResponse();
			}

			$Response->setStatus($Exception->getCode());

			if (($Route = $Router->getByName('error'))) {
				$Request->Parameters->set('Exception', $Exception);

				return $this->handleRequest($Router, $Request, 'error');
			}

			$Response = $Request->generateResponse();
			$Response->setStatus($Exception->getCode());
			$Response->setContent($Exception->getMessage());

			return $Response;
		}

		return $Response;
	}


	/**
	 * Executes a route target
	 * 
	 * @param Fol\Http\Route $Route The route to execute
	 * @param array $arguments The arguments passed to the controller
	 * 
	 * @return string The return of the controller
	 */
	protected function executeRoute (Route $Route, array $arguments = array(), array $constructor_arguments = array()) {
		ob_start();

		$target = $Route->getTarget();

		if (is_callable($target)) {
			$return = call_user_func_array($target, $arguments);
		} elseif (is_string($target)) {
			if (strpos($target, '::') !== false) {
				list($class, $method) = explode('::', $target, 2);

				$Class = new \ReflectionClass($this->namespace.'\\Controllers\\'.$class);
				$Controller = $Class->newInstanceWithoutConstructor();
				$Controller->App = $this;

				if (($Constructor = $Class->getConstructor())) {
					$Constructor->invokeArgs($Controller, $constructor_arguments);
				}

				$return = $Class->getMethod($method)->invokeArgs($Controller, $arguments);
			} else {
				$return = call_user_func_array([$this, $target], $arguments);
			}
		}

		return ob_get_clean().$return;
	}
}
