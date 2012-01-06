<?php
namespace Fol;

class App {
	public $name;
	public $path;
	public $http;
	public $real_http;

	public $Parent;
	public $Classes;



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
	 * public function execute ([fol\Request $Request])
	 *
	 * Executes the controller of the application
	 * Returns none
	 */
	public function execute (Request $Request = null) {
		if (is_null($Request)) {
			$Request = Request::createFromGlobals();
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
		} catch (\Fol\HttpException $Exception) {
			if ($controller = Router::getExceptionController($this->name, $Exception, $config['http_exception'])) {
				list($class, $method) = $controller;

				$controller = new $class($this);
				$Response = $controller->$method($Exception);
			} else {
				$Response = new Response($Exception->getMessage(), $Exception->getCode());
			}
		} catch (\ErrorException $Exception) {
			if ($controller = Router::getExceptionController($this->name, $Exception, $config['error_exception'])) {
				list($class, $method) = $controller;

				$controller = new $class($this);
				$Response = $controller->$method($Exception);
			} else {
				$Response = new Response($Exception->getMessage(), $Exception->getCode());
			}
		}

		if (!is_object($Response) || (get_class($Response) !== 'Fol\\Response')) {
			$Response = new Response($Response);
		}

		return $Response;
	}
}
?>