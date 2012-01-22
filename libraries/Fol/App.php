<?php
namespace Fol;

abstract class App {
	public $name;
	public $path;
	public $http;
	public $real_http;

	public $Parent;
	public $Services;


	/**
	 * static function create (string $name, [Object $Parent])
	 *
	 * Returns object
	 */
	static function create ($name, $Parent = null) {
		$app = 'Apps\\'.camelCase($name, true).'\\App';

		return new $app($Parent);
	}



	/**
	 * public function __construct ([Object $Parent])
	 *
	 * Returns none
	 */
	public function __construct ($Parent = null) {
		$this->Parent = $Parent;

		$Class = new \ReflectionClass($this);

		$this->name = end(explode('\\', $Class->getNameSpaceName()));
		$this->path = dirname($Class->getFileName()).'/';
		$this->http = BASE_HTTP.strtolower($this->name).'/';
		$this->real_http = BASE_HTTP.preg_replace('|^'.BASE_PATH.'|i', '', $this->path);

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
}
?>