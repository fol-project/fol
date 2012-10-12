<?php
/**
 * Fol\Http\Container
 * 
 * Simple class used to store variables (GET, POST, FILES, etc)
 */
namespace Fol\Http;

class Container {
	protected $items = array();


	/**
	 * Constructor class. You can define the items directly
	 * 
	 * @param array $items The items to store
	 */
	public function __construct (array $items = null) {
		if ($items !== null) {
			$this->set($items);
		}
	}


	/**
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
	 * Counts all stored parameteres
	 *
	 * @return int The total number of parameters
	 */
	public function length () {
		return count($this->items);
	}



	/**
	 * Gets one or all parameters.
	 * 
	 * $params->get() Returns all parameters
	 * $params->get('name') Returns just this parameter
	 * 
	 * @param string $name The parameter name
	 * @param mixed $default The default value if the parameter is not set
	 * 
	 * @return mixed The parameter value or the default
	 */
	public function get ($name = null, $default = null) {
		if ($name === null) {
			return $this->items;
		}

		if (isset($this->items[$name])) {
			return $this->items[$name];
		}

		return $default;
	}



	/**
	 * Sets one parameter or various new parameters
	 * 
	 * @param string $name The parameter name. You can define an array with name => value to insert various parameters
	 * @param mixed $value The parameter value.
	 */
	public function set ($name, $value = null) {
		if (is_array($name)) {
			$this->items = array_replace($this->items, $name);
		} else {
			$this->items[$name] = $value;
		}
	}



	/**
	 * Deletes one or all parameters
	 * 
	 * $params->delete('name') Deletes one parameter
	 * $params->delete() Deletes all parameter
	 * 
	 * @param string $name The parameter name
	 */
	public function delete ($name = null) {
		if ($name === null) {
			$this->items = array();
		} else {
			unset($this->items[$name]);
		}
	}



	/**
	 * Checks if a parameter exists
	 * 
	 * @param string $name The parameter name
	 * 
	 * @return boolean True if the parameter exists (even if it's null) or false if not
	 */
	public function exists ($name) {
		return array_key_exists($name, $this->items);
	}
}
?>