<?php
namespace Fol\Http;

use Fol\Http\Headers;
use Fol\Http\Cookies;

class Response {
	public $Headers;
	public $Cookies;

	protected $content;
	protected $status;
	protected $content_type;



	/**
	 * public function __construct ([string $content], [int $status], [array $headers])
	 *
	 */
	public function __construct ($content = '', $status = 200, array $headers = array()) {
		$this->setContent($content);
		$this->setStatus($status);
		$this->setContentType('text/html');

		$this->Headers = new Headers($headers);
		$this->Cookies = new Cookies();
	}



	/**
	 * public function __clone ()
	 *
	 * Magic function to clone the internal objects
	 */
	public function __clone () {
		$this->Headers = clone $this->Headers;
		$this->Cookies = clone $this->Cookies;
	}



	/**
	 * public function __toString ()
	 *
	 * Converts the current response to a string
	 */
	public function __toString () {
		$text = vsprintf('HTTP/1.1 %s %s', $this->status);
		$text .= "\n".sprintf('Content-Type: %s %s', $this->content_type, $this->charset)."\n";

		$text .= "\n".(string)$this->Headers;
		$text .= "\n".(string)$this->Cookies;
		$text .= "\n".$this->content;

		return $text;
	}



	/**
	 * public function setContent (string $content)
	 *
	 * Sets content
	 * Returns none
	 */
	public function setContent ($content) {
		$this->content = (string)$content;
	}



	/**
	 * public function appendContent (string $content)
	 *
	 * Appends content
	 * Returns none
	 */
	public function appendContent ($content) {
		$this->content .= (string)$content;
	}



	/**
	 * public function prependContent (string $content)
	 *
	 * Prepends content
	 * Returns none
	 */
	public function prependContent ($content) {
		$this->content = (string)$content.$this->content;
	}



	/**
	 * public function getContent (string $content)
	 *
	 * Gets content
	 * Returns string
	 */
	public function getContent () {
		return $this->content;
	}



	/**
	 * public function setStatus (int $code, [string $text])
	 *
	 * Sets the status code
	 * Returns none
	 */
	public function setStatus ($code, $text = null) {
		$this->status = array($code, ($text ?: Headers::getStatusText($code)));
	}



	/**
	 * public function getStatus ([int $text])
	 *
	 * Gets current status
	 * Returns string
	 */
	public function getStatus ($text = false) {
		return $text ? $this->status[1] : $this->status[0];
	}



	/**
	 * public function setContentType (string $type)
	 *
	 * Sets the content type header to output
	 * Returns none
	 */
	public function setContentType ($type) {
		$this->content_type = Headers::getMimeType($type) ?: $type;
	}


	/**
	 * public function getContentType ()
	 *
	 * Gets the content type header to output
	 * Returns string
	 */
	public function getContentType () {
		return $this->content_type;
	}


	/**
	 * public function send ()
	 *
	 * Sends the headers and print the content
	 * Returns none
	 */
	public function send () {
		$this->sendHeaders();
		$this->sendContent();
	}



	/**
	 * public function sendHeaders ()
	 *
	 * Sends the headers if don't have been send by the developer
	 * Returns boolean
	 */
	public function sendHeaders () {
		if (headers_sent()) {
			return false;
		}

		header(sprintf('HTTP/1.1 %s', $this->status[0], $this->status[1]));
		header(sprintf('Content-Type: %s UTF-8', $this->content_type));

		$this->Headers->send();
		$this->Cookies->send();

		return true;
	}



	/**
	 * public function sendContent ()
	 *
	 * Sends the content
	 * Returns none
	 */
	public function sendContent () {
		echo $this->content;
	}
}
?>