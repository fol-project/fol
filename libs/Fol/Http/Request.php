<?php
/**
 * Fol\Http\Request
 * 
 * Class to manage the http request data
 */
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
	private $format = 'html';


	/**
	 * Creates a new request object from global values
	 * 
	 * @return Fol\Http\Request The object with the global data
	 */
	static public function createFromGlobals () {
		$path = parse_url(preg_replace('|^'.preg_quote(BASE_URL).'|i', '', urldecode($_SERVER['REQUEST_URI'])), PHP_URL_PATH);

		return new static($path, array(), (array)filter_input_array(INPUT_GET), (array)filter_input_array(INPUT_POST), $_FILES, (array)filter_input_array(INPUT_COOKIE), (array)filter_input_array(INPUT_SERVER));
	}


	/**
	 * Creates a new request object from global values
	 *
	 * @param array $args The $argv variable with the arguments
	 * 
	 * @return Fol\Http\Request The object with the global data
	 */
	static public function createFromCli (array $args) {
		$file = array_shift($args);
		$path = array_shift($args);
		$method = 'GET';
		$parameters = [];

		if ($args) {
			if (in_array($args[0], ['GET', 'POST'])) {
				$method = array_shift($args);
			}

			while ($args) {
				$option = array_shift($args);

				if (preg_match('#^(-+)([\w]+)$#', $option, $match)) {
					$option = $match[2];
					$parameters[$option] = $args ? array_shift($args) : true;
				} else {
					$parameters[] = $option;
				}
			}
		}

		return static::create($path, $method, $parameters);
	}


	/**
	 * Creates a new request object from specified values
	 * 
	 * @param string $url The url request
	 * @param string $method The method of the request (GET, POST, PUT, DELETE)
	 * @param array $vars The parameters of the request (GET, POST, etc)
	 * @param array $parameters The extra parameters of the request
	 * 
	 * @return Fol\Http\Request The object with the specified data
	 */
	static public function create ($url, $method = 'GET', array $vars = array(), array $parameters = array()) {
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
			$defaults['SERVER_NAME'] = $defaults['HTTP_HOST'] = $components['host'];
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
			$post = $vars;
			$defaults['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
		} else {
			$get = $vars;
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

		return new static($components['path'], $parameters, $get, $post, array(), array(), $server);
	}



	/**
	 * Constructor
	 * 
	 * @param string $path The request path
	 * @param array $parameters Custom parameters of the request
	 * @param array $get The GET parameters
	 * @param array $post The POST parameters
	 * @param array $files The FILES parameters
	 * @param array $cookies The cookies of the request
	 * @param array $server The SERVER parameters
	 */
	public function __construct ($path = '', array $parameters = array(), array $get = array(), array $post = array(), array $files = array(), array $cookies = array(), array $server = array()) {
		$this->Parameters = new Container($parameters);
		$this->Get = new Input($get);
		$this->Post = new Input($post);
		$this->Files = new Files($files);
		$this->Cookies = new Input($cookies);
		$this->Server = new Container($server);
		$this->Headers = new RequestHeaders(RequestHeaders::getHeadersFromServer($server));

		foreach (array_keys($this->Headers->getParsed('Accept')) as $mimetype) {
			if ($format = Headers::getFormat($mimetype)) {
				$this->format = $format;
				break;
			}
		}

		$this->setPath($path);
	}



	/**
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

		if (isset($this->Session)) {
			$this->Session = clone $this->Session;
		}

		if (isset($this->Response)) {
			unset($this->Response);
		}
	}


	/**
	 * Magic function to initialize some properties in lazy mode
	 */
	public function __get ($name) {
		if ($name === 'Session') {
			return $this->Session = new Session();
		}

		if ($name === 'Response') {
			return $this->getResponse();
		}
	}


	/**
	 * Magic function to convert the request to a string
	 */
	public function __toString () {
		$text = "Path: ".$this->getPath();
		$text .= "\nFormat: ".$this->getFormat();
		$text .= "\nParameters:\n".$this->Parameters;
		$text .= "\nGet:\n".$this->Get;
		$text .= "\nPost:\n".$this->Post;
		$text .= "\nFiles:\n".$this->Files;
		$text .= "\nCookies:\n".$this->Cookies;
		$text .= "\nServer:\n".$this->Server;
		$text .= "\nHeaders:\n".$this->Headers;

		return $text;
	}


	/**
	 * Creates a clone of the current request with some modifications
	 *
	 * @param string $path New path for the cloned request
	 *
	 * @return Fol\Http\Request
	 */
	public function copy ($path = null, array $parameters = null) {
		$Request = clone $this;

		if ($path !== null) {
			$Request->setPath($path);
		}

		if ($parameters !== null) {
			$Request->Parameters->set($parameters);
		}

		//Use the response cookies as reference
		$Request->Response->Cookies = $this->Response->Cookies;

		//Use the same session if exists
		if (isset($this->Session)) {
			$Request->Session = $this->Session;
		}

		return $Request;
	}



	/**
	 * Gets an unique id for the request (for example for cache purposes)
	 * 
	 * @return string The id
	 */
	public function getId () {
		return md5($this->getUrl(true, true, true).' '.$this->getMethod());
	}



	/**
	 * Returns the full url
	 * 
	 * @param boolean $absolute True to return the absolute url (with scheme and host)
	 * @param boolean $format True to add the format of the request at the end of the path
	 * @param boolean $query True to add the query to the url (false by default)
	 * 
	 * @return string The current url
	 */
	public function getUrl ($absolute = true, $format = true, $query = false) {
		$url = '';

		if ($absolute === true) {
			$url .= $this->getScheme().'://';

			$url .= $this->getHost();

			if ($this->getPort() !== 80) {
				$url .= ':'.$this->getPort();
			}
		}
		
		$path = $this->getPath();

		$url .= BASE_URL.$path;

		if (($format === true) && ($path !== '/') && ($format = $this->getFormat())) {
			$url .= '.'.$format;
		}

		if (($query === true) && ($query = $this->Get->get())) {
			$url .= '?'.http_build_query($query);
		}

		return $url;
	}



	/**
	 * Gets the current path
	 * 
	 * @return string The path
	 */
	public function getPath () {
		return $this->path;
	}


	/**
	 * Sets a new current path
	 * 
	 * @param string $path The new path
	 */
	public function setPath ($path) {
		if (preg_match('/\.([\w]+)$/', $path, $match)) {
			$this->setFormat($match[1]);
			$path = preg_replace('/'.$match[0].'$/', '', $path);
		}

		if (empty($path)) {
			$path = '/';
		} elseif ($path !== '/' && (substr($path, -1) === '/')) {
			$path = substr($path, 0, -1);
		}

		$this->path = $path;
	}



	/**
	 * Gets the requested format.
	 * The format is get from the path (the extension of the requested file), or from the Accept http header
	 * 
	 * @return string The current format (html, xml, css, etc)
	 */
	public function getFormat () {
		return $this->format;
	}



	/**
	 * Sets the a new format
	 * 
	 * @param string $format The new format value
	 */
	public function setFormat ($format) {
		$this->format = strtolower($format);
	}



	/**
	 * Gets the preferred language according with the Accept-Language http header
	 * 
	 * Example:
	 * $request->getLanguage() Returns, for example gl
	 * $request->getLanguage(array('es', 'en')); Returns, for example, es
	 * 
	 * @param array $valid_languages You can define a list of valid languages, so if an accept language is in the list, returns that language. If doesn't exists, returns the first accept language.
	 * 
	 * @return string The preferred language
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
	 * Gets one parameter in POST/FILES/GET/parameters order
	 * 
	 * @param string $name The variable name
	 * @param mixed $default The default value if the variable does not exists in POST, FILES or GET values
	 * 
	 * @return mixed The value of the variable or the default value
	 */
	public function get ($name, $default = null) {
		return $this->Post->get($name, $this->Files->get($name, $this->Get->get($name, $this->Parameters->get($name, $default))));
	}



	/**
	 * Removes a variable in POST/FILES/GET/parameters
	 * 
	 * @param string $name The variable name to remove
	 */
	public function remove ($name) {
		$this->Post->remove($name);
		$this->Files->remove($name);
		$this->Get->remove($name);
		$this->Parameters->remove($name);
	}



	/**
	 * Check if a variable exists in POST/FILES/GET/parameters
	 * 
	 * @param string $name The variable name to check
	 * 
	 * @return boolean TRUE if the variable exists in any of the parameters and FALSE if doesn't
	 */
	public function has ($name) {
		return ($this->Post->has($name) || $this->Files->has($name) || $this->Get->has($name) || $this->Parameters->has($name)) ? true : false;
	}


	/**
	 * Returns the real client IP
	 * 
	 * @return string The client IP
	 */
	public function getIp () {
		return $this->Server->get('HTTP_CLIENT_IP', $this->Server->get('HTTP_X_FORWARDED_FOR', $this->Server->get('REMOTE_ADDR')));
	}


	/**
	 * Detects if the request has been made by ajax or not
	 * 
	 * @return boolean TRUE if the request if ajax, FALSE if not
	 */
	public function isAjax () {
		return (strtolower($this->Server->get('HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest') ? true : false;
	}


	/**
	 * Gets the request scheme
	 * 
	 * @return string The request scheme (http or https)
	 */
	public function getScheme () {
		return ($this->Server->get('HTTPS') === 'on') ? 'https' : 'http';
	}



	/**
	 * Gets the request host
	 * 
	 * @return string The request host
	 */
	public function getHost () {
		return $this->Server->get('SERVER_NAME');
	}


	
	/**
	 * Gets the port on which the request is made
	 * 
	 * @return int The port number
	 */
	public function getPort () {
		return intval($this->Server->get('X_FORWARDED_PORT') ?: $this->Server->get('SERVER_PORT'));
	}



	/**
	 * Gets the request method
	 * 
	 * @return string The request method (in uppercase: GET, POST, etc)
	 */
	public function getMethod () {
		$method = $this->Server->get('REQUEST_METHOD', 'GET');
	
		if ($method === 'POST') {
			$this->method = strtoupper($this->Server->get('X_HTTP_METHOD_OVERRIDE', 'POST'));
		}

		return $method;
	}


	/**
	 * Returns the response instance for this request
	 * 
	 * @return Fol\Http\Response
	 */
	public function getResponse ($content = '', $status = 200, array $headers = array()) {
		if (!isset($this->Response)) {
			$this->Response = new Response($content, $status, $headers);
			$this->Response->setContentType($this->getFormat());
		}

		return $this->Response;
	}


	/**
	 * Check if the response has been modified or not
	 * 
	 * @return boolean True if the response is not modified, false if is modified or there isn't cache headers
	 */
	public function responseIsNotModified () {
		$RequestHeaders = $this->Headers;
		$ResponseHeaders = $this->Response->Headers;

		$hasCondition = false;

		if ($RequestHeaders->has('If-Modified-Since')) {
			$hasCondition = true;

			if ($RequestHeaders->getDateTime('If-Modified-Since')->getTimestamp() < $ResponseHeaders->getDateTime('Last-Modified')->getTimestamp()) {
				return false;
			}
		}

		if ($ResponseHeaders->has('Expires')) {
			$hasCondition = true;

			if ($ResponseHeaders->getDateTime('Expires')->getTimestamp() < time()) {
				return false;
			}
		}

		return $hasCondition;
	}


	/**
	 * Defines a If-Modified-Since header
	 * 
	 * @param string/Datetime $datetime
	 */
	public function setIfModifiedSince ($datetime) {
		$this->Headers->setDateTime('If-Modified-Since', $datetime);
	}
}
