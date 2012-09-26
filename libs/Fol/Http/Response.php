<?php
/**
 * Fol\Http\Response
 * 
 * Class to manage the http response data
 */
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
	 * Creates a response that redirects to another url
	 * 
	 * @param string $url The new url to redirect
	 * 
	 * @return Fol\Http\Response The response object
	 */
	static public function createRedirect ($url) {
		$Response = new static;

		$Response->Headers->set('location', $url);
		$Response->setStatus(301);

		return $Response;
	}


	/**
	 * Constructor
	 * 
	 * @param string $content The body of the response
	 * @param integer $status The status code (200 by default)
	 * @param array $headers The headers to send in the response 
	 */
	public function __construct ($content = '', $status = 200, array $headers = array()) {
		$this->setContent($content);
		$this->setStatus($status);
		$this->setContentType('text/html');

		$this->Headers = new Headers($headers);
		$this->Cookies = new Cookies();
	}



	/**
	 * Magic function to clone the internal objects
	 */
	public function __clone () {
		$this->Headers = clone $this->Headers;
		$this->Cookies = clone $this->Cookies;
	}



	/**
	 * Magic function to converts the current response to a string
	 */
	public function __toString () {
		return (string)$this->content;
	}



	/**
	 * Sets the content of the response body
	 * 
	 * @param string $content The text content
	 */
	public function setContent ($content) {
		$this->content = (string)$content;
	}



	/**
	 * Appends more content to the response body
	 * 
	 * @param string $content The text content to append
	 */
	public function appendContent ($content) {
		$this->content .= (string)$content;
	}



	/**
	 * Prepends content to the response body
	 * 
	 * @param string $content The text content to prepend
	 */
	public function prependContent ($content) {
		$this->content = (string)$content.$this->content;
	}



	/**
	 * Gets the body content
	 * 
	 * @return string The body of the response
	 */
	public function getContent () {
		return $this->content;
	}



	/**
	 * Sets the status code
	 * 
	 * @param integer $code The status code (for example 404)
	 * @param string $text The status text. If it's not defined, the text will be the defined in the Fol\Http\Headers:$status array
	 */
	public function setStatus ($code, $text = null) {
		$this->status = array($code, ($text ?: Headers::getStatusText($code)));
	}



	/**
	 * Gets current status
	 * 
	 * @param string $text Set to TRUE to return the status text instead the status code
	 * 
	 * @return integer The status code or the status text if $text parameter is true
	 */
	public function getStatus ($text = false) {
		return $text ? $this->status[1] : $this->status[0];
	}



	/**
	 * Sets the content type header to output
	 * 
	 * @param string $type The mimetype or format of the output (for example "css" or "text/css")
	 */
	public function setContentType ($type) {
		$this->content_type = Headers::getMimeType($type) ?: $type;
	}


	/**
	 * Gets the content type header to output
	 * 
	 * @return string The mime type
	 */
	public function getContentType () {
		return $this->content_type;
	}


	/**
	 * Sends the headers and the content
	 */
	public function send () {
		$this->sendHeaders();
		$this->sendContent();
	}


	/**
	 * Sends the headers if don't have been sent before
	 * 
	 * @return boolean TRUE if the headers are sent and false if headers had been sent before
	 */
	public function sendHeaders () {
		if (headers_sent()) {
			return false;
		}

		header(sprintf('HTTP/1.1 %s', $this->status[0], $this->status[1]));
		header(sprintf('Content-Type:%s;charset=utf-8', $this->content_type));

		$this->Headers->send();
		$this->Cookies->send();

		return true;
	}


	/**
	 * Sends the content
	 */
	public function sendContent () {
		echo $this->content;
	}
}
?>
