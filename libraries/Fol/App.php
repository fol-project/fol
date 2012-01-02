<?php
namespace Fol;

class App {
	public $name;
	public $path;
	public $http;
	public $real_http;

	public $Request;
	public $Classes;



	/**
	 * static function create (string $name, [Fol\Request $Request])
	 *
	 * Returns object
	 */
	static function create ($name, Request $Request = null) {
		$app = 'Apps\\'.camelCase($name, true).'\\App';

		if (is_null($Request)) {
			$Request = Request::createFromGlobals();
		}

		return new $app($Request);
	}



	/**
	 * public function __construct (Fol\Request $Request)
	 *
	 * Returns none
	 */
	public function __construct (Request $Request) {
		$this->Request = $Request;

		$Class = new \ReflectionClass($this);

		$this->name = end(explode('\\', $Class->getNameSpaceName()));
		$this->path = dirname($Class->getFileName()).'/';
		$this->http = BASE_HTTP.strtolower($this->name).'/';
		$this->real_http = BASE_HTTP.preg_replace('|^'.BASE_PATH.'|i', '', $this->path);

		$this->Classes = new Containers\Classes;
		$this->Classes->set('Config', 'Fol\\Config', array($this->path.'config/'));
	}



	/**
	 * public function __get (string $name)
	 *
	 * Returns object
	 */
	public function __get ($name) {
		return $this->$name = $this->Classes->getInstance($name);
	}



	/**
	 * public function setEnvironment (string $environment)
	 *
	 * Sets an application environment
	 * Returns none
	 */
	public function setEnvironment ($environment) {
		$this->environment = $environment;
		$this->Config->setEnvironment($environment);
	}



	/**
	 * public function getEnvironment ()
	 *
	 * Gets an application environment
	 * Returns string
	 */
	public function getEnvironment () {
		return $this->environment;
	}



	/**
	 * public function execute ()
	 *
	 * Executes the controller of the application
	 * Returns none
	 */
	public function execute ($Request = null) {
		if (is_null($Request)) {
			$Request = $this->Request;
		} else if (is_string($Request)) {
			$url = $Request;
			$Request = clone $this->Request;
			$Request->setUrl($url);
		}

		$config = $this->Config->get('controllers');

		try {
			$controller = Router::getController($this->name, $Request, $config);

			if ($controller) {
				list($class, $method, $parameters, $path) = $controller;

				$Request->Path->set($path);

				$controller = new $class($this, $Request);
				$Response = call_user_func_array(array($controller, $method), $parameters);
			} else {
				exception(Response::$status[404], 404);
			}
		} catch (\Fol\Exception $Exception) {
			if ($controller = Router::getExceptionController($this->name, $Exception, $config)) {
				list($class, $method) = $controller;

				$controller = new $class($this);
				$Response = $controller->$method($Exception);
			} else {
				$Response = new Response($Exception->getMessage(), $Exception->getCode());
			}
		}

		if (!is_object($Response) || get_class($Response) !== 'Fol\\Response') {
			$Response = new Response($Response);
		}

		return $Response;
	}
}
?>