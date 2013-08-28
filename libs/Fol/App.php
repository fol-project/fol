<?php
/**
 * Fol\App
 * 
 * This is the abstract class that all apps must extend. Provides the basic functionality parameters (paths, urls, namespace, parent, etc)
 */

namespace Fol;

use Fol\Router\Map;
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
}
