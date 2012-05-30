<?php
namespace Fol\Http;

use Fol\Http\Container;
use Fol\Http\Input;
use Fol\Http\Files;
use Fol\Http\Headers;

class Request {
	public $Parameters;
	public $Get;
	public $Post;
	public $Files;
	public $Cookies;
	public $Headers;
	public $Server;

	private $path;
	private $format;



	/**
	 * static function createFromGlobals (void)
	 *
	 * Creates a new request object from global values
	 * Returns object
	 */
	static public function createFromGlobals () {
		$path = parse_url(preg_replace('|^'.preg_quote(BASE_HTTP).'|', '', $_SERVER['REQUEST_URI']), PHP_URL_PATH);

		return new static($path, array(), (array)filter_input_array(INPUT_GET), (array)filter_input_array(INPUT_POST), $_FILES, (array)filter_input_array(INPUT_COOKIE), (array)filter_input_array(INPUT_SERVER));
	}


	/**
	 * static function create (string $url, [string $method], [array $parameters])
	 *
	 * Creates a new request object from specified values
	 * Returns object
	 */
	static public function create ($url, $method = 'GET', array $parameters = array()) {
		$defaults = array(
			'SERVER_NAME' => 'localhost',
			'SERVER_PORT' => 80,
			'HTTP_HOST' => 'localhost',
			'HTTP_USER_AGENT' => 'FOL/'.FOL_VERSION,
			'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
			'HTTP_ACCEPT_LANGUAGE' => 'gl-es,es,en;q=0.5',
			'HTTP_ACCEPT_CHARSET' => 'utf-8;q=0.7,*;q=0.7',
			'REMOTE_ADDR' => '127.0.0.1',
			'SCRIPT_NAME' => '',
			'SCRIPT_FILENAME' => '',
			'SERVER_PROTOCOL' => 'HTTP/1.1',
			'REQUEST_TIME' => time(),
		);

		$components = parse_url($url);

		if (isset($components['host'])) {
			$defaults['SERVER_NAME'] = $components['host'];
			$defaults['HTTP_HOST'] = $components['host'];
		}

		if (isset($components['scheme']) && ($components['scheme'] === 'https')) {
			$defaults['HTTPS'] = 'on';
			$defaults['SERVER_PORT'] = 443;
		}

		if (isset($components['port'])) {
			$defaults['SERVER_PORT'] = $components['port'];
			$defaults['HTTP_HOST'] = $defaults['HTTP_HOST'].':'.$components['port'];
		}

		if (!isset($components['path'])) {
			$components['path'] = '';
		}

		$components['query'] = isset($components['query']) ? html_entity_decode($components['query']) : '';

		$post = $get = array();

		if (in_array(strtoupper($method), array('POST', 'PUT', 'DELETE'))) {
			$post = $parameters;
			$defaults['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
		} else {
			$get = $parameters;
		}

		if ($components['query']) {
			parse_str($components['query'], $query);

			$get = array_replace($query, $get);
		}

		$server = array_replace($defaults, array(
			'REQUEST_METHOD' => strtoupper($method),
			'PATH_INFO' => '',
			'REQUEST_URI' => $url,
			'QUERY_STRING' => $components['query'],
		));

		return new static($components['path'], array(), $get, $post, array(), array(), $server);
	}



	/**
	 * public function __construct ([string $path], [array $parameters], [array $get], [array $post], [array $files], [array $cookies], [array $server])
	 *
	 */
	public function __construct ($path = '', array $parameters = array(), array $get = array(), array $post = array(), array $files = array(), array $cookies = array(), array $server = array()) {
		$this->Parameters = new Container($parameters);
		$this->Get = new Input($get);
		$this->Post = new Input($post);
		$this->Files = new Files($files);
		$this->Cookies = new Input($cookies);
		$this->Server = new Container($server);
		$this->Headers = new Headers(Headers::getHeadersFromServer($server));

		foreach (array_keys($this->Headers->getParsed('Accept')) as $mimetype) {
			if ($format = Headers::getFormat($mimetype)) {
				$this->format = $format;
				break;
			}
		}

		$this->setPath($path);
	}



	/**
	 * public function __clone ()
	 *
	 * Magic function to clone the internal objects
	 */
	public function __clone () {
		$this->Parameters = clone $this->Parameters;
		$this->Get = clone $this->Get;
		$this->Post = clone $this->Post;
		$this->Files = clone $this->Files;
		$this->Cookies = clone $this->Cookies;
		$this->Server = clone $this->Server;
		$this->Headers = clone $this->Headers;
	}


	/**
	 * public function __toString ()
	 *
	 * Converts the request to a string
	 */
	public function __toString () {
		$text = "Parameters:\n".$this->Parameters;
		$text .= "\nGet:\n".$this->Get;
		$text .= "\nPost:\n".$this->Post;
		$text .= "\nFiles:\n".$this->Files;
		$text .= "\nCookies:\n".$this->Cookies;
		$text .= "\nServer:\n".$this->Server;
		$text .= "\nHeaders:\n".$this->Headers;

		return $text;
	}



	/**
	 * public function getId ()
	 *
	 * Gets an unique id for the request (for cache purposes)
	 * Returns string
	 */
	public function getId () {
		return md5(serialize(array(
			$this->getPath(),
			$this->Parameters->get(),
			$this->Get->get(),
			$this->Post->get(),
			$this->Files->get()
		)));
	}



	/**
	 * public function getPath ()
	 *
	 * Gets the current path
	 * Returns string
	 */
	public function getPath () {
		return $this->path;
	}



	/**
	 * public function setPath (string $path)
	 *
	 * Sets a new current path
	 * Returns none
	 */
	public function setPath ($path) {
		if (preg_match('/\.([\w]+)$/', $path, $match)) {
			$this->setFormat($match[1]);
			$path = preg_replace('/'.$match[0].'$/', '', $path);
		}

		$this->path = $path;
	}



	/**
	 * public function getFormat ()
	 *
	 * Gets the requested format
	 * Returns string/false
	 */
	public function getFormat () {
		return $this->format ?: false;
	}



	/**
	 * public function setFormat (string $format)
	 *
	 * Sets the a new format
	 * Returns none
	 */
	public function setFormat ($format) {
		$this->format = strtolower($format);
	}



	/**
	 * public function getLanguage ([array $valid_languages])
	 *
	 * Gets the preferred language
	 * Returns none
	 */
	public function getLanguage (array $valid_languages = null) {
		$user_languages = array_keys($this->Headers->getParsed('Accept-Language'));

		if (is_null($valid_languages)) {
			return $user_languages[0];
		}

		if (!$user_languages) {
			return $valid_languages[0];
		}

		$common_languages = array_values(array_intersect($user_languages, $valid_languages));

		return $common_languages[0] ?: $valid_languages[0];
	}



	/**
	 * public function get ($name, [mixed $default])
	 *
	 * Gets one parameter in POST/FILES/GET/parameters order
	 * Returns mixed
	 */
	public function get ($name, $default = null) {
		return $this->Post->get($name, $this->Files->get($name, $this->Get->get($name, $this->Parameters->get($name, $default))));
	}



	/**
	 * public function getQueryString ()
	 *
	 * Gets the GET variables as a string
	 * Returns string
	 */
	public function getQueryString () {
		if ($query = $this->Get->get()) {
			return http_build_query($query);
		}

		return '';
	}



	/**
	 * public function remove (string $name)
	 *
	 * Removes a variable in POST/FILES/GET/parameters
	 * Returns none
	 */
	public function remove ($name) {
		$this->Post->remove($name);
		$this->Files->remove($name);
		$this->Get->remove($name);
		$this->Parameters->remove($name);
	}



	/**
	 * public function exists (string $name)
	 *
	 * Check if a variable exists in POST/FILES/GET/parameters
	 * Returns boolean
	 */
	public function exists ($name) {
		return ($this->Post->exists($name) || $this->Files->exists($name) || $this->Get->exists($name) || $this->Parameters->exists($name)) ? true : false;
	}


	/**
	 * public function getIp ()
	 *
	 * Returns the real client IP
	 * Returns string
	 */
	public function getIp () {
		return $this->Server->get('HTTP_CLIENT_IP', $this->Server->get('HTTP_X_FORWARDED_FOR', $this->Server->get('REMOTE_ADDR')));
	}


	/**
	 * public function isAjax ()
	 *
	 * Detects if the request has been made by ajax or not
	 * Returns boolean
	 */
	public function isAjax () {
		return (strtolower($this->Server->get('HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest') ? true : false;
	}


	/**
	 * public function getScheme ()
	 *
	 * Gets the request scheme
	 * Returns string
	 */
	public function getScheme () {
		return ($this->Server->get('HTTPS') === 'on') ? 'https' : 'http';
	}


	
	/**
	 * public function getPort ()
	 *
	 * Gets the port on which the request is made
	 * Returns string
	 */
	public function getPort () {
		return $this->Server->get('X_FORWARDED_PORT') ?: $this->Server->get('SERVER_PORT');
	}



	/**
	 * public function getMethod ()
	 *
	 * Gets the request method in uppercase
	 * Returns string
	 */
	public function getMethod () {
		$method = strtolower($this->Server->get('REQUEST_METHOD', 'get'));
	
		if ($method === 'post') {
			$this->method = strtoupper($this->Server->get('X_HTTP_METHOD_OVERRIDE', 'post'));
		}

		return $method;
	}
}
?>