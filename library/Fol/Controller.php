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
				$autoloads = $this->Config->get('autoloads');

				if ($autoloads[$name]) {
					return $this->$name = new $autoloads[$name];
				}
		}
	}
}
?>