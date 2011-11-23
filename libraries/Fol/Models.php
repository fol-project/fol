<?php
namespace Fol;

class Models {

	/**
	 * public function __get ($name)
	 *
	 * Returns mixed
	 */
	public function __get ($name) {
		$class = 'Models\\'.$name;

		if (class_exists($class)) {
			return $this->$name = new $class;
		}
	}
}
?>