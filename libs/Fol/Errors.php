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
	static protected $logger;
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


	static public function setLogger (LoggerInterface $logger) {
		static::$logger = $logger;
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

			ini_set('display_errors', '0');
			ini_set('display_startup_errors', '0');

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

			ini_set('display_errors', get_cfg_var('display_errors'));
			ini_set('display_startup_errors', get_cfg_var('display_startup_errors'));

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
	static public function handleException (\Exception $exception) {
		foreach (static::$handlers as $handler) {
			$handler($exception);
		}

		if (static::$displayErrors) {
			if (php_sapi_name() === 'cli') {
				echo static::getTextException($exception);
			} else {
				echo static::getHtmlException($exception);
			}
		}

		if (isset(static::$logger)) {
			static::$logger->error($exception->getMessage(), ['exception' => $exception]);
		}
	}


	/**
	 * Returns a exception info as HTML
	 * 
	 * @param Exception $exception
	 */
	static public function getHtmlException (\Exception $exception, $deep = 0) {
		$previous = ($previousException = $exception->getPrevious()) ? self::getHtmlException($previousException, $deep + 1) : '';
		$class = get_class($exception);
		$date = ($deep === 0) ? '<time>'.date('r').'</time><br>' : '';

		return <<<EOT
<section id="ErrorException">
	{$date}
	<h1>{$exception->getMessage()} ({$exception->getCode()})</h1>
	<p>
		<em>{$class}</em><br>
		{$exception->getFile()}:{$exception->getLine()}<br>
	</p>
	<pre>{$exception->getTraceAsString()}</pre>
	{$previous}
</section>
EOT;
	}


	/**
	 * Returns a exception info as text (for CLI)
	 * 
	 * @param Exception $exception
	 */
	static public function getTextException (\Exception $exception, $deep = 0) {
		$previous = ($previousException = $exception->getPrevious()) ? self::getTextException($previousException, $deep + 1) : '';
		$class = get_class($exception);

		return (($deep === 0) ? "\n=======================\n".date('r')."\n\n" : "\n----------\n")
			."{$exception->getMessage()} ({$exception->getCode()})\n"
			."{$class} | {$exception->getFile()}:{$exception->getLine()}\n\n"
			.$exception->getTraceAsString()
			.$previous
			.(($deep === 0) ? "\n=======================\n" : "\n");
	}
}
