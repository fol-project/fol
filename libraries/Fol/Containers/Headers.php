<?php
namespace Fol\Containers;

class Headers extends Container {

	/**
	 * public function __construct (array $parameters)
	 *
	 * Detects request info
	 * Returns none
	 */
	public function __construct (array $parameters = array()) {
		foreach ($parameters as $name => $value) {
			$this->set($name, $value);
		}
	}



	/**
	 * public function set (string $name, mixed $value, [boolean $replace])
	 *
	 * Sets one parameter
	 * Returns none
	 */
	public function set ($name, $value, $replace = true) {
		if ($replace || !isset($this->items[$name])) {
			$this->items[$name] = (array)$value;
		} else {
			$this->items[$name] = array_merge($this->items[$name], (array)$value);
		}
	}
}
?>