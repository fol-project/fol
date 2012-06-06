<?php
namespace Fol;

class Errors {
	static private $level;


	/**
	 * static public function register ([int $level])
	 *
	 * Register the error handler.
	 * Returns Error instance
	 */
	static public function register ($level = null) {
		self::setLevel($level);

		set_error_handler(__NAMESPACE__.'\\Errors::handle');
	}


	/**
	 * static public function unregister ()
	 *
	 * Register the error handler.
	 * Returns Error instance
	 */
	static public function unregister () {
		restore_error_handler();
	}


	/**
	 * static public public function setLevel ($level)
	 *
	 * Sets the error level.
	 * Returns none
	 */
	static public function setLevel ($level) {
		self::$level = is_null($level) ? error_reporting() : $level;
	}


	/**
	 * static public function handle (int $level, string $message, string $file, string $line)
	 *
	 * throws ErrorException when error_reporting returns error
	 * Returns false
	 */
	static public function handle ($level, $message, $file, $line) {
		if (self::$level === 0) {
			return false;
		}

		if (error_reporting() & $level && self::$level & $level) {
			throw new \ErrorException($message, $level, $level, $file, $line);
		}

		return false;
	}
}
?>