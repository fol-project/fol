<?php
namespace Fol;

class Cache {
	public $item;

	/**
	 * public function __get ($name)
	 *
	 * Returns object/none
	 */
	public function __get ($name) {
		$class = 'Fol\\Cache\\'.$name;

		if (class_exists($class)) {
			return $this->$name = new $class;
		}
	}
}
?>