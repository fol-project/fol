<?php
/**
 * Fol\Http\Input
 * 
 * Class to store all input variables ($_GET, $_POST)
 */
namespace Fol\Http;

class Input extends Container {

	/**
	 * Gets one or all parameters. You can gets the subvalues using brackets:
	 * 
	 * $input->get('user') Returns, for example: array('name' => 'xan', 'age' => 34)
	 * $input->get('user[age]') Returns 34
	 * 
	 * @param string $name The parameter name
	 * @param mixed $default A default value to return if the name does not exist
	 * 
	 * @return mixed The value or default value
	 */
	public function get ($name = null, $default = null) {
		if (is_string($name) && (strpos($name, '[') !== false) && (strpos($name, ']') !== false)) {
			$subarrays = explode('[', str_replace(']', '', $name));
			$value = $this->items;

			while ($subarrays) {
				$value = $value[array_shift($subarrays)];
			}

			if (isset($value)) {
				return $value;
			}
		}

		return parent::get($name, $default);
	}



	/**
	 * Returns a value filtered using the filter_var php function
	 * 
	 * For example:
	 * $input->filter('email', FILTER_VALIDATE_EMAIL)
	 * 
	 * @param string $name The variable name
	 * @param integer $filter One of the available filters provided by php (http://www.php.net/manual/en/filter.filters.php)
	 * @param integer $options Options for the filter
	 * 
	 * @return mixed The filtered value
	 */
	public function filter ($name, $filter, $options = null) {
		$value = $this->get($name);

		if (is_null($value)) {
			return $value;
		}

		return ($options === null) ? filter_var($value, $filter) : filter_var($value, $filter, $options);
	}
}
?>