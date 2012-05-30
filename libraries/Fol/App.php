<?php
namespace Fol;

abstract class App {
	private $namespace;
	private $name;
	private $path;
	private $http;

	public $Parent;


	/**
	 * static function create (string $name, [Object $Parent])
	 *
	 * Returns object
	 */
	static function create ($name, App $Parent = null, $httpPath = null) {
		$name = str_replace(' ', '', ucwords(strtolower(str_replace('-', ' ', $name))));

		$app = 'Apps\\'.$name.'\\App';

		if (class_exists($app)) {
			return new $app($Parent, $httpPath);
		}

		throw new InvalidArgumentException('"'.$app.'" is an invalid app class');
	}



	/**
	 * public function __construct ([Object $Parent])
	 *
	 * Returns none
	 */
	public function __construct (App $Parent = null, $httpPath = null) {
		$Class = new \ReflectionClass($this);

		$this->namespace = $Class->getNameSpaceName();
		$this->name = substr(strrchr($this->namespace, '\\'), 1);
		$this->path = dirname($Class->getFileName()).'/';

		if (isset($Parent)) {
			$this->Parent = $Parent;
			$httpPath = $Parent->getHttpPath().$httpPath;
		}

		$this->setHttpPath($httpPath);
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
		if (!empty($http) && substr($http, -1) !== '/') {
			$http .= '/';
		}

		$this->http = strtolower($http);
	}


	public function getHttpPath () {
		return $this->http;
	}
}
?>