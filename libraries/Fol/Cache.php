<?php
namespace Fol;

class Cache {
	private $settings;


	/**
	 * public function __construct ([array $settings])
	 *
	 * Returns object/none
	 */
	public function __construct ($settings = array()) {
		$this->settings = $settings;
	}



	/**
	 * public function __get ($name)
	 *
	 * Returns object/none
	 */
	public function __get ($name) {
		$class = 'Fol\\Cache_'.$name;

		if (class_exists($class)) {
			return $this->$name = new $class($this->settings[$name]);
		}
	}
}
?>