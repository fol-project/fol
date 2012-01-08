<?php
namespace Fol;

class Models {
	private $App;



	/**
	 * public function __construct (object $App)
	 *
	 * Returns none
	 */
	public function __construct ($App) {
		$this->App = $App;
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