<?php
namespace Fol;

class Models {
	private $Controller;

	/**
	 * public function __construct ($Controller)
	 *
	 * Returns mixed
	 */
	public function __construct ($Controller) {
		$this->Controller = $Controller;
	}



	/**
	 * public function __get ($name)
	 *
	 * Returns mixed
	 */
	public function __get ($name) {
		$class = 'Models\\'.$name;

		if (class_exists($class)) {
			return $this->$name = new $class($this->Controller);
		}
	}
}
?>