<?php
namespace Fol;

class Config {
	public $environment;

	private $folder;
	private $items = array();



	/**
	 * public function __construct ([string $folder])
	 *
	 * Returns none
	 */
	public function __construct ($folder = null) {
		$this->setFolder($folder);
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
	 * public function setFolder (string $folder)
	 *
	 * Returns boolean
	 */
	public function setFolder ($folder) {
		if (is_dir($folder)) {
			$this->folder = $folder;

			return true;
		}

		throw new ErrorException('The folder config "'.$folder.'" does not exists');
	}



	/**
	 * public function getFolder ()
	 *
	 * Returns string
	 */
	public function getFolder () {
		return $this->folder;
	}



	/**
	 * public function load (string $name)
	 *
	 * Loads the data of config files (in php or ini format)
	 * Returns mixed
	 */
	public function load ($name) {
		if (!$this->folder) {
			throw new ErrorException('The folder config is not set');

			return false;
		}

		$file = $name.'.php';

		if ($this->environment && file_exists($this->folder.$this->environment.'/'.$name.'.php')) {
			$config = include($this->folder.$this->environment.'/'.$name.'.php');
		} else if ($this->environment && file_exists($this->folder.$this->environment.'/'.$name.'.ini')) {
			$config = parse_ini_file($this->folder.$this->environment.'/'.$name.'.ini', true);
		} else if (file_exists($this->folder.$name.'.php')) {
			$config = include($this->folder.$name.'.php');
		} else if (file_exists($this->folder.$name.'.ini')) {
			$config = parse_ini_file($this->folder.$name.'.ini', true);
		}

		$this->set($name, $config);

		return $config;
	}



	/**
	 * public function get ([string $name])
	 *
	 * Returns mixed
	 */
	public function get ($name = null) {
		if (func_num_args() === 0) {
			return $this->items;
		}

		if (!isset($this->items[$name])) {
			$this->load($name);
		}

		return $this->items[$name];
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
	 * public function delete (string $name, [string $key])
	 *
	 *
	 * Deletes a variable
	 * Returns none
	 */
	public function delete ($name, $key = null) {
		if ($key) {
			unset($this->items[$name][$key]);

			if (!$this->items[$name]) {
				unset($this->items[$name]);
			}
		} else {
			unset($this->items[$name]);
		}
	}



	/**
	 * public function exists (string $name, [string $key])
	 *
	 * Checks if a parameter exists
	 * Returns boolean
	 */
	public function exists ($name, $key = null) {
		if (!array_key_exists($name, $this->items)) {
			return false;
		}

		return $key ? array_key_exists($key, $this->items[$name]) : true;
	}
}
?>