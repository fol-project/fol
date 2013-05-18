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
	protected $headers_sent = false;


	public static function __set_state ($array) {
		$Response = new static($array['content'], $array['status'][0]);
		$Response->setContentType($array['content_type']);

		$Response->Headers = $array['Headers'];
		$Response->Cookies = $array['Cookies'];

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

		$this->Headers = new ResponseHeaders($headers);
		$this->Cookies = new Cookies();

		if (!$this->Headers->has('Date')) {
			$this->Headers->setDateTime('Date', new \DateTime());
		}
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
		$this->status = array($code, ($text ?: ResponseHeaders::getStatusText($code)));
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
	 * Set the status code and header needle to redirect to another url
	 * 
	 * @param  string  $url    The url of the new location
	 * @param  integer $status The http code to redirect (302 by default)
	 */
	public function redirect ($url, $status = 302) {
		$this->setStatus($status);
		$this->Headers->set('location', $url);
	}


	/**
	 * Defines a Not Modified status
	 */
	public function setNotModified () {
		$this->setStatus(304);
		$this->setContent('');

		foreach (array('Allow', 'Content-Encoding', 'Content-Language', 'Content-Length', 'Content-MD5', 'Content-Type', 'Last-Modified') as $name) {
			$this->Headers->remove($header);
		}
	}


	/**
	 * Sends the headers and the content
	 */
	public function send () {
		if (!$this->headers_sent) {
			$this->sendHeaders();
			$this->headers_sent = true;
		}

		$this->sendContent();
	}


	/**
	 * Send the output buffer and empty the response content
	 */
	public function flush () {
		$this->send();
		
		flush();

		if (ob_get_level() > 0) {
			ob_flush();
		}

		$this->content = '';
	}


	/**
	 * Sends the headers if don't have been sent before
	 * 
	 * @return boolean TRUE if the headers are sent and false if headers had been sent before
	 */
	public function sendHeaders () {
		if (headers_sent()) {
			throw new \Exception('Cannot send headers because they have been send before');
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


	/**
	 * Defines a Last-Modified header
	 * 
	 * @param string/Datetime $datetime
	 */
	public function setLastModified ($datetime) {
		$this->Headers->setDateTime('Last-Modified', $datetime);
	}



	/**
	 * Defines a Expire header
	 * 
	 * @param string/Datetime $datetime
	 */
	public function setExpires ($datetime) {
		$this->Headers->setDateTime('Expires', $datetime);
	}



	/**
	 * Returns the age of the response
	 * 
	 * @return integer The age in seconds
	 */
	public function getAge () {
		if ($this->Headers->has('Age')) {
			return (int)$this->Headers->get('Age');
		}

		return max(time() - $this->Headers->getDateTime('Date')->getTimestamp(), 0);
	}


	/**
	 * Defines a max-age and optionally s-maxage cache directive
	 *
	 * @param int $max_age The max age in seconds
	 * @param int $shared_max_age The shared max age in seconds
	 */
	public function setMaxAge ($max_age, $shared_max_age = null) {
		$cacheControl = $this->Headers->getParsed('Cache-Control');
		$cacheControl['max-age'] = (int)$max_age;

		if ($shared_max_age !== null) {
			$cacheControl['s-maxage'] = (int)$shared_max_age;
		}

		$this->Headers->setParsed('Cache-Control', $cacheControl);
	}


	/**
	 * Returns the max-age cache directive
	 *
	 * @return int $age The age in seconds
	 */
	public function getMaxAge () {
		$cacheControl = $this->Headers->getParsed('Cache-Control');

		if (isset($cacheControl['s-maxage'])) {
			return (int)$cacheControl['s-maxage'];
		}

		return isset($cacheControl['max-age']) ? (int)$cacheControl['max-age'] : 0;
	}


	/**
	 * Defines the response as public in Cache-Control directive
	 */
	public function setPublic () {
		$cacheControl = $this->Headers->getParsed('Cache-Control');
		$cacheControl['public'] = true;
		unset($cacheControl['private']);
		$this->Headers->setParsed('Cache-Control', $cacheControl);
	}


	/**
	 * Defines the response as private in Cache-Control directive
	 */
	public function setPrivate () {
		$cacheControl = $this->Headers->getParsed('Cache-Control');
		$cacheControl['private'] = true;
		unset($cacheControl['public']);
		$this->Headers->setParsed('Cache-Control', $cacheControl);
	}


	/**
	 * Check if the response must be revalidated by the origin
	 */
	public function mustRevalidate () {
		$cacheControl = $this->Headers->getParsed('Cache-Control');

		return (!empty($cacheControl['must-revalidate']) || $this->Headers->has('proxy-revalidate'));
	}


	/**
	 * Add a must-revalidate cache control directive
	 */
	public function setMustRevalidate () {
		$cacheControl = $this->Headers->getParsed('Cache-Control');
		$cacheControl['must-revalidate'] = true;
		$this->Headers->setParsed('Cache-Control', $cacheControl);
	}
}
