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
				$scene = $this->Config->get('scene');

				if ($scene['autoload'][$name]) {
					return $this->$name = new $scene['autoload'][$name];
				}
		}
	}
}
?>