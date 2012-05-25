<?php
namespace Fol;

abstract class App {
	private $namespace;
	private $name;
	private $path;
	private $http;

	public $Parent;
	public $Services;


	/**
	 * static function create (string $name, [Object $Parent])
	 *
	 * Returns object
	 */
	static function create ($name, $Parent = null) {
		$name = str_replace(' ', '', ucwords(strtolower(str_replace('-', ' ', $name))));

		$app = 'Apps\\'.$name.'\\App';

		if (class_exists($app)) {
			return new $app($Parent);
		}

		throw new InvalidArgumentException('"'.$app.'" is an invalid app class');
	}



	/**
	 * public function __construct ([Object $Parent])
	 *
	 * Returns none
	 */
	public function __construct ($Parent = null) {
		$this->Parent = $Parent;

		$Class = new \ReflectionClass($this);

		$this->namespace = $Class->getNameSpaceName();
		$this->name = substr(strrchr($this->namespace, '\\'), 1);
		$this->path = dirname($Class->getFileName()).'/';

		$this->setHttpPath($this->name);

		$this->Services = new Services;
	}



	/**
	 * abstract public function bootstrap ()
	 *
	 * Runs the application
	 * Returns none
	 */
	abstract public function bootstrap ();



	/**
	 * public function __get (string $name)
	 *
	 * Returns object
	 */
	public function __get ($name) {
		return $this->$name = $this->Services->get($name);
	}


	public function getName () {
		return $this->name;
	}


	public function getNameSpace () {
		return $this->namespace;
	}


	public function getPath () {
		return $this->path;
	}


	public function setHttpPath ($http) {
		if ($http[0] === '/') {
			$http = BASE_HTTP.$http;
		}
		
		if (substr($http, -1) !== '/') {
			$http .= '/';
		}

		$this->http = strtolower($http);
	}


	public function getHttpPath () {
		return $this->http;
	}
}
?>