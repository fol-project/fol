<?php
namespace Fol\Http;

class Container {
	protected $items = array();


	/**
	 * public function __construct (array $items)
	 *
	 * Returns none
	 */
	public function __construct (array $items = array()) {
		$this->set($items);
	}


	/**
	 * public function __toString ()
	 *
	 * Converts all items to a string
	 */
	public function __toString () {
		$text = '';

		foreach ($this->items as $name => $value) {
			if (is_array($value)) {
				$value = json_encode($value);
			}

			$text .= "$name: $value\n";
		}

		return $text;
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
			$this->items = array_replace($this->items, $name);
		} else {
			$this->items[$name] = $value;
		}
	}



	/**
	 * public function delete ([string $name])
	 *
	 * Deletes one parameter
	 * Returns none
	 */
	public function delete ($name = null) {
		if (func_num_args() === 0) {
			$this->items = array();
		} else {
			unset($this->items[$name]);
		}
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
}
?>