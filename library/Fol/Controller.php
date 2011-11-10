<?php
namespace Fol;

class Controller {

	/**
	* public function __get ($name)
	*
	* Returns mixed
	*/
	public function __get ($name) {
		switch ($name) {
			case 'Actions':
			case 'Templates':
			case 'Session':
				$class = 'Fol\\'.$name;
				return $this->$name = new $class;

			case 'Config':
			case 'Input':
			case 'Output':
			case 'Router':
				global $$name;
				return $this->$name = $$name;
		}
	}
}
?>