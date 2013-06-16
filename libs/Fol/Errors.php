<?php
/**
 * Fol\Errors
 * 
 * A simple class to handle all php errors.
 */
namespace Fol;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class Errors {
	static protected $handlers = array();
	static protected $Logger;
	static protected $isRegistered = false;
	static protected $displayErrors = false;


	/**
	 * Enable or disable the error displaying
	 *
	 * @param boolean $display True to display, false to not
	 */
	static public function displayErrors ($display = true) {
		static::$displayErrors = $display;
	}


	static public function setLogger (LoggerInterface $Logger) {
		static::$Logger = $Logger;
	}


	/**
	 * Pushes a handler to the end of the stack.
	 *
	 * @param callable $handler The callback to execute
	 */
	static public function pushHandler ($handler) {
		if (!is_callable($handler)) {
			throw new \InvalidArgumentException('The argument to '.__METHOD__.' is not callable');
		}

		static::$handlers[] = $handler;
	}


	/**
	 * Removes the last handler and returns it
	 *
	 * @return callable or null
	 */
	static public function popHandler () {
		return array_pop(static::$handlers);
	}


	/**
	 * Register the error handler.
	 */
	static public function register () {
		if (!static::$isRegistered) {
			set_error_handler(__NAMESPACE__.'\\Errors::handleError');
			set_exception_handler(__NAMESPACE__.'\\Errors::handleException');
			register_shutdown_function(__NAMESPACE__.'\\Errors::handleShutdown');

			static::$isRegistered = true;
		}
	}


	/**
	 * Unregister the error handler. Restore the error handler to previous status.
	 */
	static public function unregister () {
		if (static::$isRegistered) {
			restore_error_handler();
			restore_exception_handler();

			static::$isRegistered = false;
		}
	}


	/**
	 * Converts a php error to an exception and handle it
	 * 
	 * @param int $level The error level
	 * @param string $message The error message
	 * @param string $file The file when the error is
	 * @param int $file The number of the line when the error is
	 */
	static public function handleError ($level, $message, $file = null, $line = null) {
		if (error_reporting() & $level) {
			static::handleException(new \ErrorException($message, $level, 0, $file, $line));
		}
	}


	/**
	 * Converts a php shutdown error to an exception and handle it
	 */
	static public function handleShutdown () {
		if (static::$isRegistered && ($error = error_get_last())) {
			static::handleError($error['type'], $error['message'], $error['file'], $error['line']);
		}
	}


	/**
	 * Execute all registered callbacks
	 * 
	 * @param Exception The exception passed to the callbacks
	 */
	static public function handleException (\Exception $Exception) {
		foreach (static::$handlers as $handler) {
			$handler($Exception);
		}

		if (static::$displayErrors) {
			echo static::printException($Exception);
		}

		if (isset(static::$Logger)) {
			static::saveExceptionLog($Exception);
		}
	}

	/**
	 * Print the exception info as html
	 * 
	 * @param Exception $Exception
	 */
	static protected function printException (\Exception $Exception) {
		if (($Previous = $Exception->getPrevious())) {
			$previous = self::printException($Previous);
		} else {
			$previous = '';
		}

		$class = get_class($Exception);

		echo <<<EOT
<section id="ErrorException">
	<h1>{$Exception->getMessage()} ({$Exception->getCode()})</h1>
	<p>
		<em>{$class}</em><br>
		{$Exception->getFile()}:{$Exception->getLine()}
	</p>
	<pre>{$Exception->getTraceAsString()}</pre>
	{$previous}
</section>
EOT;
	}


	/**
	 * Saves a exception in the logger
	 * 
	 * @param  Exception $Exception
	 */
	static public function saveExceptionLog (\Exception $Exception) {
		$level = $Exception->getCode();

		switch ($level) {
			case 100:
				$level = LogLevel::DEBUG;
				break;

			case 200:
				$level = LogLevel::INFO;
				break;

			case 250:
				$level = LogLevel::NOTICE;
				break;

			case 300:
				$level = LogLevel::WARNING;
				break;

			case 400:
				$level = LogLevel::ERROR;
				break;

			case 500:
				$level = LogLevel::CRITICAL;
				break;

			case 550:
				$level = LogLevel::ALERT;
				break;

			case 600:
				$level = LogLevel::EMERGENCY;
				break;

			default:
				$level = LogLevel::ERROR;
		}

		static::log($level, $Exception->getMessage(), ['exception' => $Exception]);
	}

	/**
	 * Save a log in the logger
	 * 
	 * @param mixed $level
	 * @param string $message
	 * @param  array  $context
	 */
	static public function log ($level, $message, array $context = array()) {
		if (static::$Logger) {
			static::$Logger->log($level, $Exception->getMessage(), ['exception' => $Exception]);
		}
	}
}
