<?php
namespace Fol;

class Models {
	private $App;



	/**
	 * public function __construct (object $Controller)
	 *
	 * Returns none
	 */
	public function __construct ($Controller) {
		$this->App = $Controller->App;
		$this->Controller = $Controller;
	}



	/**
	 * public function __get (string $name)
	 *
	 * Returns object
	 */
	public function __get ($name) {
		$class = 'Apps\\'.$this->App->name.'\\Models\\'.$name;

		if (class_exists($class)) {
			return $this->$name = new $class($this->App);
		}
	}
}
?>