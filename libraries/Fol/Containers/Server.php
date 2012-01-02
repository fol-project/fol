<?php
namespace Fol\Containers;

class Server extends Container {

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
	 * public function get ([string $name], [mixed $default])
	 *
	 * Gets one or all parameters
	 * Returns mixed
	 */
	public function get ($name = null, $default = null) {
		if (func_num_args() === 0) {
			return $this->items;
		}

		if (is_string($name)) {
			$name = strtr(strtolower($name), '_', '-');
		}

		if (!empty($this->items[$name])) {
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
				$this->set($key, $value);
			}

			return;
		}

		$this->items[strtr(strtolower($name), '_', '-')] = $value;
	}



	/**
	 * public function exists (string $name)
	 *
	 * Checks if a parameter exists
	 * Returns boolean
	 */
	public function exists ($name) {
		return array_key_exists(strtolower($name), $this->items);
	}



	/**
	 * public function getHttpAcceptHeader ([string $name])
	 *
	 * Parse and return http accept headers
	 * Returns boolean
	 */
	public function getHttpAcceptHeader ($name = 'http_accept') {
		preg_match_all('#([^,;]+)(;q=([0-9\.]+))?#', $this->get($name), $matches, PREG_SET_ORDER);

		$results = array();

		foreach ($matches as $match) {
			$results[trim($match[1])] = isset($match[3]) ? floatval($match[3]) : 1;
		}

		return $results;
	}
}
?>