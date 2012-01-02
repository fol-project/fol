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

	private $url = array();
	private $format;



	/**
	 * static function createFromGlobals (void)
	 *
	 * Creates a new request object from global values
	 * Returns object
	 */
	static public function createFromGlobals () {
		return new static(getenv('REQUEST_URI'), array(), (array)filter_input_array(INPUT_GET), (array)filter_input_array(INPUT_POST), $_FILES, (array)filter_input_array(INPUT_COOKIE), (array)filter_input_array(INPUT_SERVER));
	}



	/**
	 * public function __construct ([string $url], [array $path], [array $get], [array $post], [array $files], [array $cookies], [array $server])
	 *
	 */
	public function __construct ($url = '', array $path = array(), array $get = array(), array $post = array(), array $files = array(), array $cookies = array(), array $server = array()) {
		$this->url = parse_url(preg_replace('|^'.preg_quote(BASE_HTTP).'|', '', $url), PHP_URL_PATH);

		if (preg_match('/\.([\w]+)$/', $this->url, $match)) {
			$this->format = strtolower($match[1]);
			$this->url = preg_replace('/'.$match[0].'$/', '', $this->url);
		}

		$this->Path = new Parameters($path);
		$this->Get = new Parameters($get);
		$this->Post = new Parameters($post);
		$this->Files = new Files($files);
		$this->Cookies = new Parameters($cookies);
		$this->Server = new Server($server);
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
		return md5(serialize(array($this->url, $this->Path->get(), $this->Get->get(), $this->Post->get(), $this->Files->get())));
	}



	/**
	 * public function getUrl ()
	 *
	 * Gets the current url
	 * Returns string
	 */
	public function getUrl () {
		return $this->url;
	}



	/**
	 * public function setUrl (string $url)
	 *
	 * Sets a new current url
	 * Returns none
	 */
	public function setUrl ($url) {
		$this->url = $url;
		$this->Path->removeAll();
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
		$this->format = $format;
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