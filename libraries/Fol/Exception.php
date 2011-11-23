<?php
namespace Fol;

class Exception extends \Exception {

	/**
	 * public function __construct (string $message, [int $code])
	 *
	 * Returns none
	 */
	public function __construct ($message, $code = 500) {
		parent::__construct($message, $code);
	}


	/**
	 * public function getController ()
	 *
	 * Returns none
	 */
	public function getController () {
		global $Config;

		$config = $Config->get('routes');

		if ($class = $config['exceptions'][$this->getCode()]) {
			list($class, $method) = explodeTrim(':', $class);

			$class = '\\Controllers\\'.$class;

			if (class_exists($class)) {
				return array($class, $method);
			}
		}

		return false;
	}



	/**
	 * public function runController ()
	 *
	 * Returns none
	 */
	public function runController () {
		if ($class = $this->getController()) {
			$Exception = new $class[0];
			$Exception->$class[1]($this->getMessage());
		}
	}
}
?>