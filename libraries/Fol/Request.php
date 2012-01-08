<?php
namespace Fol;

use Fol\Containers\Parameters;
use Fol\Containers\Files;
use Fol\Containers\Server;

class Request {
	public $Path;
	public $Get;
	public $Post;
	public $Files;
	public $Cookies;
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
		$path = parse_url(preg_replace('|^'.preg_quote(BASE_HTTP).'|', '', getenv('REQUEST_URI')), PHP_URL_PATH);

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
		$this->Path = new Parameters($parameters);
		$this->Get = new Parameters($get);
		$this->Post = new Parameters($post);
		$this->Files = new Files($files);
		$this->Cookies = new Parameters($cookies);
		$this->Server = new Server($server);

		$this->setPath($path);
	}



	/**
	 * public function __clone ()
	 *
	 * Magic function to clone the internal objects
	 */
	public function __clone () {
		$this->Path = clone $this->Path;
		$this->Get = clone $this->Get;
		$this->Post = clone $this->Post;
		$this->Files = clone $this->Files;
		$this->Cookies = clone $this->Cookies;
		$this->Server = clone $this->Server;
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
			$this->Path->get(),
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
		$this->Path->clear();
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
	 * public function get ($name, [mixed $default])
	 *
	 * Gets one parameter in POST/FILES/GET/PATH order
	 * Returns mixed
	 */
	public function get ($name, $default = null) {
		return $this->Post->get($name, $this->Files->get($name, $this->Get->get($name, $this->Path->get($name, $default))));
	}



	/**
	 * public function remove (string $name)
	 *
	 * Removes a variable in POST/FILES/GET/PATH
	 * Returns none
	 */
	public function remove ($name) {
		$this->Post->remove($name);
		$this->Files->remove($name);
		$this->Get->remove($name);
		$this->Path->remove($name);
	}



	/**
	 * public function exists (string $name)
	 *
	 * Check if a variable exists in POST/FILES/GET/PATH
	 * Returns boolean
	 */
	public function exists ($name) {
		return ($this->Post->exists($name) || $this->Files->exists($name) || $this->Get->exists($name) || $this->Path->exists($name)) ? true : false;
	}


	/**
	 * public function getIp ()
	 *
	 * Returns the real client IP
	 * Returns string
	 */
	public function getIp () {
		return $this->Server->get('http-client-ip', $this->Server->get('http-x-forwarded-for', $this->Server->get('remote-addr')));
	}


	/**
	 * public function isAjax ()
	 *
	 * Detects if the request has been made by ajax or not
	 * Returns boolean
	 */
	public function isAjax () {
		return (strtolower($this->Server->get('http-x-requested-with')) === 'xmlhttprequest') ? true : false;
	}


	/**
	 * public function getScheme ()
	 *
	 * Gets the request scheme
	 * Returns string
	 */
	public function getScheme () {
		return ($this->Server->get('https') === 'on') ? 'https' : 'http';
	}


	
	/**
	 * public function getPort ()
	 *
	 * Gets the port on which the request is made
	 * Returns string
	 */
	public function getPort () {
		return $this->Server->get('x-forwarded-port') ?: $this->Server->get('server-port');
	}
}
?>