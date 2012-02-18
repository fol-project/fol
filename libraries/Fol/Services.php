<?php
namespace Fol;

class Services {
	private $items;

	private static $shared;


	/**
	 * public function getRegister ([string $name])
	 *
	 * Gets one or all registered services
	 * Returns array
	 */
	public function getRegister ($name = null) {
		if (func_num_args() === 0) {
			return $this->items;
		}

		if ($data = $this->items[$name]) {
			return $data;
		}
	}



	/**
	 * public function register (string $name, [string $class], [array $parameters], [bool $shared])
	 *
	 * Register a new service
	 * Returns none
	 */
	public function register ($name, $class = null, array $parameters = array(), $shared = false) {
		if (is_array($name)) {
			foreach ($name as $class) {
				$this->register($name, $class[0], $class[1], (array)$class[2], $class[3]);
			}

			return;
		}

		$this->items[$name] = array(
			'class' => $class,
			'parameters' => $parameters,
			'shared' => $shared
		);
	}



	/**
	 * public function unregister (string $name)
	 *
	 * Deletes a service
	 * Returns none
	 */
	public function unregister ($name) {
		unset($this->items[$name]);
	}



	/**
	 * public function registered (string $name)
	 *
	 * Check if a service is registered
	 * Returns boolean
	 */
	public function registered ($name) {
		return isset($this->items[$name]);
	}



	/**
	 * public function get (string $service, [array $arguments])
	 *
	 * Gets a service
	 * Returns object/false
	 */
	public function get ($service, $arguments = null) {
		$data = $this->items[$name];

		if (!$data['shared']) {
			return $this->newInstance($service, $arguments);
		}

		if (self::$shared) {
			return self::$shared;
		}

		return self::$shared = $this->newInstance($service, $arguments);
	}



	/**
	 * private function newInstance (string $service, [array $parameters])
	 *
	 * Create a new instance of a class
	 * Returns object/false
	 */
	private function newInstance ($service, $parameters = null) {
		if ($data = $this->items[$service]) {
			if (class_exists($data['class'])) {
				$Class = new \ReflectionClass($data['class']);

				if ($Class->isInstantiable()) {
					$parameters = is_array($parameters) ? array_replace($data['parameters'], $parameters) : (array)$data['parameters'];

					if ($parameters && $Class->hasMethod('__construct')) {
						if ($parameters = $this->sortParameters($Class->getMethod('__construct'), $parameters)) {
							$Instance = $Class->newInstanceArgs($parameters);
						} else {
							throw new \BadMethodCallException('The class "'.$data['class'].'" has not __construct so you cannot set parameters for instantialization');
							return false;
						}
					} else {
						$Instance = $Class->newInstance();
					}

					if ($data['on_construct'] && is_callable($data['on_construct'])) {
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

		return false;
	}


	/**
	 * private function sortParameters (ReflectionMethod $Method, array $parameters)
	 *
	 * Convert an associative array of parameters to numerical
	 * Returns array/false
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