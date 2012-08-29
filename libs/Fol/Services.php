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
	 * Gets one or all registered services
	 * 
	 * @param string $name The service name. If it's not defined, returns all services
	 * 
	 * @return array The registered service with all data (class name, parameters and if it's shared) or false if it doesn't exists
	 */
	public function getRegister ($name = null) {
		if (func_num_args() === 0) {
			return $this->items;
		}

		return isset($this->items[$name]) ? $this->items[$name] : false;
	}



	/**
	 * Register a new service
	 * 
	 * @param string $name The service name
	 * @param string $class The class name of the service
	 * @param array $parameters an array of parameters to use in the constructor class
	 * @param bool $shared If it's shared, only just one instance will be created and shared across all request
	 */
	public function register ($name, $class = null, array $parameters = array(), $shared = false) {
		$this->items[$name] = array(
			'class' => $class,
			'parameters' => $parameters,
			'shared' => $shared
		);
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
	 * @param array $arguments If its defined, use this arguments in the instantiation of the service
	 * 
	 * @return object The class instance or false if the service is not defined
	 */
	public function get ($name, array $arguments = null) {
		if (($data = $this->getRegister($name)) === false) {
			return false;
		}

		if (isset($arguments)) {
			$data['parameters'] = array_replace($data['parameters'], $arguments);
		}

		if (!$data['shared']) {
			return $this->newInstance($data);
		}

		if (isset(self::$shared[$name])) {
			return self::$shared[$name];
		}

		return self::$shared[$name] = $this->newInstance($data);
	}



	/**
	 * Private function to create a new instance of a class
	 * 
	 * @param array $data The service data (class, parameters)
	 * 
	 * @throws InvalidArgumentException or BadMethodCallException on error
	 * 
	 * @return object The class instance or false if the service is not defined
	 */
	private function newInstance ($data) {
		if (class_exists($data['class'])) {
			$Class = new \ReflectionClass($data['class']);

			if ($Class->isInstantiable()) {
				if ($data['parameters'] && $Class->hasMethod('__construct')) {
					if ($parameters = $this->sortParameters($Class->getMethod('__construct'), $data['parameters'])) {
						$Instance = $Class->newInstanceArgs($parameters);
					} else {
						throw new \BadMethodCallException('The class "'.$data['class'].'" has not __construct so you cannot set parameters for instantialization');
						return false;
					}
				} else {
					$Instance = $Class->newInstance();
				}

				if (isset($data['on_construct']) && is_callable($data['on_construct'])) {
					$data['on_construct']($this, $Instance);
				}

				return $Instance;
			} else {
				throw new \InvalidArgumentException('The class "'.$data['class'].'" is not instantiable');
			}
		} else {
			throw new \InvalidArgumentException('The class "'.$data['class'].'" does not exists');
		}
	}


	/**
	 * Convert an associative array of parameters to numerical
	 * 
	 * @param ReflectionMethod $Method The method reflection class
	 * @param array $parameters The list of parameters
	 * 
	 * @return The new array with the parameters sorted or false if some parameters are missing
	 */
	private function sortParameters (\ReflectionMethod $Method, array $parameters) {
		$args = array();

		foreach ($Method->getParameters() as $Parameter) {
			$name = $Parameter->getName();
			$pos = $Parameter->getPosition();

			if (array_key_exists($name, $parameters)) {
				$args[] = is_callable($parameters[$name]) ? $parameters[$name]($this) : $parameters[$name];
			} else if (array_key_exists($pos, $parameters)) {
				$args[] = is_callable($parameters[$pos]) ? $parameters[$pos]($this) : $parameters[$pos];
			} else if ($Parameter->isOptional()) {
				$args[] = $Parameter->getDefaultValue();
			} else {
				return false;
			}
		}

		return $args;
	}
}
?>