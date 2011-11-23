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
			case 'Config':
			case 'Input':
			case 'Output':
			case 'Router':
				global $$name;
				return $this->$name = $$name;

			default:
				$autoload = $this->Config->get('controller', 'autoload');

				if ($autoload[$name]) {
					return $this->$name = new $autoload[$name];
				}
		}
	}
}
?>