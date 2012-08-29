<?php
/**
 * Fol\Errors
 * 
 * A simple class to handle all errors.
 */
namespace Fol;

class Errors {
	static private $level;


	/**
	 * Register the error handler.
	 * 
	 * Fol\Errors::register(E_ALL);
	 * 
	 * @param int $level The error level you want to notify
	 */
	static public function register ($level = null) {
		self::setLevel($level);

		set_error_handler(__NAMESPACE__.'\\Errors::handle');
	}


	/**
	 * Unregister the error handler. Restore the error handler to previous status.
	 */
	static public function unregister () {
		restore_error_handler();
	}


	/**
	 * Sets the error level. The errors lower than the level will be silentiated
	 * 
	 * @param int $level The error level you want to notify
	 */
	static public function setLevel ($level) {
		self::$level = is_null($level) ? error_reporting() : $level;
	}


	/**
	 * Throws ErrorException when error_reporting returns error and the error level is equal or upper.
	 * 
	 * @param int $level The error level
	 * @param string $message The error message
	 * @param string $file The file when the error is
	 * @param int $file The number of the line when the error is
	 * 
	 * @return false
	 */
	static public function handle ($level, $message, $file, $line) {
		if (self::$level === 0) {
			return false;
		}

		if ((error_reporting() & $level) && (self::$level & $level)) {
			throw new \ErrorException($message, $level, $level, $file, $line);
		}

		return false;
	}
}
?>