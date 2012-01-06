<?php
namespace Fol;

class Errors {
	private $level;


	/**
	 * static public function register ([int $level])
	 *
	 * Register the error handler.
	 * Returns Error instance
	 */
	static public function register ($level = null) {
		$Errors = new static();
		$Errors->setLevel($level);

		set_error_handler(array($Errors, 'handle'));

		return $Errors;
	}


	/**
	 * public public function setLevel ($level)
	 *
	 * Sets the error level.
	 * Returns none
	 */
	public function setLevel ($level) {
		$this->level = is_null($level) ? error_reporting() : $level;
	}


	/**
	 * public function handle (int $level, string $message, string $file, string $line)
	 *
	 * throws ErrorException when error_reporting returns error
	 * Returns false
	 */
	public function handle ($level, $message, $file, $line) {
		if ($this->level === 0) {
			return false;
		}

		if (error_reporting() & $level && $this->level & $level) {
			throw new \ErrorException($message, $level, $level, $file, $line);
		}

		return false;
	}
}
?>