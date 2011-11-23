<?php
namespace Fol;

class Config {
	public $item = array();
	private $paths = array();



	/**
	 * public function load (string $name)
	 *
	 * Returns mixed
	 */
	public function load ($name) {
		$file = $name.'.php';
		$basedir = SCENE_PATH.'config/';

		$config = array();

		if (file_exists($basedir.ENVIRONMENT.'/'.$file)) {
			include ($basedir.ENVIRONMENT.'/'.$file);
		} else if (file_exists($basedir.$file)) {
			include ($basedir.$file);
		}

		$this->set($name, $config, $context);

		return $config;
	}



	/**
	 * public function get (string $name, [string $key])
	 *
	 * Returns mixed
	 */
	public function get ($name, $key = null) {
		if (!isset($this->item[$name])) {
			$this->load($name);
		}

		return $key ? $this->item[$name][$key] : $this->item[$name];
	}



	/**
	 * public function set (string $name, mixed $value)
	 *
	 * Returns none
	 */
	public function set ($name, $value) {
		$this->item[$name] = $value;
	}



	/**
	 * public function delete (string $name, [string $key])
	 *
	 * Returns none
	 */
	public function delete ($name, $key = null) {
		if ($key) {
			unset($this->item[$name][$key]);
		} else {
			unset($this->item[$name]);
		}
	}



	/**
	 * public function add (string $name, mixed $value)
	 *
	 * Returns none
	 */
	public function add ($name, $value) {
		if (is_array($value)) {
			$this->item[$name] = arrayMergeReplaceRecursive((array)$this->item[$name], $value);
		} else {
			$this->item[$name] = $value;
		}
	}
}
?>