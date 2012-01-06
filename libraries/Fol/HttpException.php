<?php
namespace Fol;

class HttpException extends \Exception {

	/**
	 * public function __construct (string $message, [int $code])
	 *
	 * Returns none
	 */
	public function __construct ($message, $code = 500) {
		parent::__construct($message, $code);
	}
}
?>