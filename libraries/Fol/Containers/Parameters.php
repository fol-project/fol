<?php
namespace Fol\Containers;

class Parameters extends Container {

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

		if ((strpos($name, '[') !== false) && (strpos($name, ']') !== false)) {
			$subarrays = explode('[', str_replace(']', '', $name));
			$value = $this->items;

			while ($subarrays) {
				$value = $value[array_shift($subarrays)];
			}

			if (isset($value)) {
				return $value;
			}
		}

		return $default;
	}



	/**
	 * public function getNumerical ()
	 *
	 * Gets all numeric parameters
	 * Returns array
	 */
	public function getNumerical () {
		$parameters = array();

		foreach ($this->items as $name => $value) {
			if (is_int($name)) {
				$parameters[$name] = $value;
			}
		}

		return $parameters;
	}



	/**
	 * public function filter (string $name, int $filter, [mixed $options])
	 *
	 * Filters a value or an array of values
	 * Returns mixed
	 */
	public function filter ($name, $filter, $options = null) {
		$value = $this->get($name);

		if (is_null($value)) {
			return $value;
		}

		return is_null($options) ? filter_var($value, $filter) : filter_var($value, $filter, $options);
	}
}
?>