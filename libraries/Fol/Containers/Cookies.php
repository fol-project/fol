<?php
namespace Fol\Containers;

class Cookies extends Container {
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
	 * public function length ()
	 *
	 * Returns the total number of parameters
	 * Returns integer
	 */
	public function length () {
		return count($this->items);
	}



	/**
	 * public function get ([string $name], [boolean $all_info])
	 *
	 * Gets one or all parameters
	 * Returns mixed
	 */
	public function get ($name = null, $all_info = false) {
		if (func_num_args() === 0) {
			if ($all_info) {
				return $this->items;
			}

			$values = array();

			foreach ($this->items as $name => $item) {
				$values[$name] = $item['value'];
			}

			return $values;
		}

		if (isset($this->items[$name])) {
			return $all_info ? $this->items[$name] : $this->items[$name]['value'];
		}
	}



	/**
	 * public function set (string $name, [mixed $value], [int $expire], [string $path], [string $domain], [boolean $secure], [boolean $http_only])
	 * public function set (array $values)
	 *
	 * Sets one parameter
	 * Returns none
	 */
	public function set ($name, $value = null, $expire = 0, $path = '/', $domain = null, $secure = false, $http_only = true) {
		if (is_array($name)) {
			foreach ($name as $key => $value) {
				$this->set($key, $value);
			}

			return;
		}

		if ($expire instanceof \DateTime) {
			$expire = $expire->format('U');
		} else if (!is_numeric($expire)) {
			$expire = strtotime($expire);
		}

		$this->items[$name] = array(
			'name' => $name,
			'value' => $value,
			'domain' => $domain,
			'expire' => $expire,
			'path' => empty($path) ? '/' : $path,
			'secure' => (Boolean)$secure,
			'http_only' => (Boolean)$http_only
		);
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
}
?>