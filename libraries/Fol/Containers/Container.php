<?php
namespace Fol\Containers;

class Container {
	protected $items = array();



	/**
	 * public function __construct (array $parameters)
	 *
	 * Detects request info
	 * Returns none
	 */
	public function __construct (array $parameters = array()) {
		$this->items = $parameters;
	}



	/**
	 * public function length ()
	 *
	 * Returns the total number of parameters
	 * Returns integer
	 */
	public function length () {
		return count($this->items);
	}



	/**
	 * public function get ([string $name], [mixed $default])
	 *
	 * Gets one or all parameters
	 * Returns mixed
	 */
	public function get ($name = null, $default = null) {
		if (func_num_args() === 0) {
			return $this->items;
		}

		if (isset($this->items[$name])) {
			return $this->items[$name];
		}

		return $default;
	}



	/**
	 * public function set (string $name, mixed $value)
	 * public function set (array $values)
	 *
	 * Sets one parameter
	 * Returns none
	 */
	public function set ($name, $value = null) {
		if (is_array($name)) {
			foreach ($name as $key => $value) {
				$this->items[$key] = $value;
			}

			return;
		}

		$this->items[$name] = $value;
	}



	/**
	 * public function delete (string $name)
	 *
	 * Deletes one parameter
	 * Returns none
	 */
	public function delete ($name) {
		unset($this->items[$name]);
	}



	/**
	 * public function clear ()
	 *
	 * Deletes all parameters
	 * Returns none
	 */
	public function clear () {
		$this->items = array();
	}




	/**
	 * public function exists (string $name)
	 *
	 * Checks if a parameter exists
	 * Returns boolean
	 */
	public function exists ($name) {
		return array_key_exists($name, $this->items);
	}



	/**
	 * public function replace (array $values)
	 *
	 * Replaces recursively some values
	 * Returns none
	 */
	public function replace (array $values) {
		$this->items = array_replace_recursive((array)$this->items, $values);
	}



	/**
	 * public function reset (array $items)
	 *
	 * Reset all items with new values
	 * Returns none
	 */
	public function reset (array $items) {
		$this->clear();
		$this->set($items);
	}
}
?>