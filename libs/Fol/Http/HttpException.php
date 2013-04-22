<?php
/**
 * Fol\Http\HttpException
 * 
 * Exception to throw if an http error happens (for example 404, 500, etc)
 */
namespace Fol\Http;

class HttpException extends \Exception {

	/**
	 * Constructor.
	 * 
	 * @param string $message The http message
	 * @param integer $code The http error code. By default is 500
	 */
	public function __construct ($message, $code = 500, $previous = null) {
		parent::__construct($message, $code, $previous);
	}
}
?>
