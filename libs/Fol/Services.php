<?php
/**
 * Fol\Services
 * 
 * A class to manage dependecy injection 
 */
namespace Fol;

class Services {
	private $items;

	private static $shared;

	/**
	 * Magic method to register automatically unshared services
	 */
	public function __set ($name, $value) {
		$this->register($name, $value);
	}


	/**
	 * Magic method to get automatically the registered services
	 */
	public function __get ($name) {
		$this->$name = $this->get($name);
	}


	/**
	 * Register a new service
	 * 
	 * @param string $name The service name
	 * @param Closure $resolve A function that returns a service instance
	 * @param boolean $shared Set true to share this service in all service intances
	 */
	public function register ($name, \Closure $resolve, $shared = false) {
		$this->items[$name] = $resolve;

		if ($shared === true) {
			if (array_key_exists($name, self::$shared)) {
				throw new \Exception("The shared service '$name' is already registered");
			}

			self::$shared[$name] = null;
		}
	}



	/**
	 * Deletes a service
	 * 
	 * @param string $name The service name
	 */
	public function unregister ($name) {
		unset($this->items[$name]);
	}



	/**
	 * Check if a service is registered
	 * 
	 * @param string $name The servize name
	 */
	public function registered ($name) {
		return isset($this->items[$name]);
	}



	/**
	 * Gets an instance of a service
	 * 
	 * @param string $name The service name
	 * @param mixed $argument1 
	 * @param mixed $argument2
	 * ... 
	 * 
	 * @return object The class instance or false if the service is not defined
	 */
	public function get ($name) {
		if (isset(self::$shared[$name])) {
			return self::$shared[$name];
		}

		if ($this->registered($name) === false) {
			throw new Exception("There is not any service registered with the name '$name'");
		}

		$instance = call_user_func_array($this->items[$name], func_get_args());

		if (array_key_exists($name, self::$shared)) {
			self::$shared[$name] = $instance;
		}

		return $instance;
	}
}
?>