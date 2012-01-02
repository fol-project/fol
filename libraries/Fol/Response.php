<?php
namespace Fol;

use Fol\Containers\Headers;

class Response {
	public $Headers;

	protected $content;
	protected $status_code;
	protected $status_text;
	protected $content_type;
	protected $charset;

	static public $status = array(
		100 => 'Continue',
		101 => 'Switching Protocols',
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		307 => 'Temporary Redirect',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		418 => 'I\'m a teapot',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
	);

	static public $content_types = array(
		'js' => 'text/js',
		'json' => 'text/json',
		'html' => 'text/html',
		'css' => 'text/css',
		'gif' => 'image/gif',
		'jpeg' => 'image/jpeg',
		'jpg' => 'image/jpg',
		'png' => 'image/png',
		'pdf' => 'application/pdf',
		'zip' => 'application/zip',
		'txt' => 'text/plain',
	);



	/**
	 * public function __construct ([string $content], [int $status], [array $headers])
	 *
	 */
	public function __construct ($content = '', $status = 200, array $headers = array()) {
		$this->setContent($content);
		$this->setStatus($status);
		$this->setCharset('UTF-8');
		$this->setContentType('text/html');

		$this->Headers = new Headers($headers);
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
		$this->status_code = $code;
		$this->status_text = $text ?: self::$status[$code];
	}



	/**
	 * public function getStatus ([int $text])
	 *
	 * Gets current status
	 * Returns string
	 */
	public function getStatus ($text = false) {
		return $text ? $this->status_text : $this->status_code;
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

		header(sprintf('HTTP/1.0 %s %s', $this->status_code, $this->status_text));
		header(sprintf('Content-Type: %s %s', $this->content_type, $this->charset));

		foreach ($this->Headers->get() as $headers) {
			foreach ($headers as $name => $value) {
				header($name.': '.$value, false);
			}
		}

		return true;
	}



	/**
	 * public function sendContent ()
	 *
	 * Sends the content
	 * Returns boolean
	 */
	public function sendContent () {
		echo $this->content;
	}



	/**
	 * public function setContentType (string $type)
	 *
	 * Sets the content type header to output
	 * Returns none
	 */
	public function setContentType ($type) {
		$this->content_type = self::$content_types[$type] ?: $type;
	}


	/**
	 * public function getContentType ()
	 *
	 * Gets the content type header to output
	 * Returns string
	 */
	public function getContentType ($type) {
		return $this->content_type;
	}



	/**
	 * public function setCharset (string $charset)
	 *
	 * Sets the charset
	 * Returns none
	 */
	public function setCharset ($charset) {
		$this->charset = $charset;
	}


	/**
	 * public function getCharset ()
	 *
	 * Gets the charset
	 * Returns string
	 */
	public function getCharset () {
		return $this->charset;
	}
}
?>