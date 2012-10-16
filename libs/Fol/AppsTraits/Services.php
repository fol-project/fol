<?php
/**
 * Fol\Services
 * 
 * Provides a simple dependecy injection manager
 */
namespace Fol\AppsTraits;

trait Services {
	private $services;


	/**
	 * Register a new service
	 * 
	 * @param string $name The service name
	 * @param Closure $resolve A function that returns a service instance
	 */
	public function register ($name, \Closure $resolve = null) {
		if (is_array($name)) {
			foreach ($name as $name => $resolve) {
				$this->register($name, $resolve);
			}

			return;
		}

		$this->services[$name] = $resolve;
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
	 * Check if a service is registered
	 * 
	 * @param string $name The servize name
	 */
	public function registered ($name) {
		return isset($this->services[$name]);
	}



	/**
	 * Gets an instance of a service
	 * 
	 * @param string $name The service name
	 * 
	 * @return object The class instance or false if the service is not defined
	 */
	public function get ($name) {
		if ($this->registered($name) === false) {
			throw new \Exception("There is not any service registered with the name '$name'");
		}

		return $this->services[$name]();
	}


	/**
	 * Magic function to generate and save the services
	 * 
	 * @param string $name The service name
	 */
	public function __get ($name) {
		if ($this->registered($name)) {
			return $this->$name = $this->get($name);
		}

		return parent::__get($name);
	}
}
?>