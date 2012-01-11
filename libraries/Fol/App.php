<?php
namespace Fol;

abstract class App {
	public $name;
	public $path;
	public $http;
	public $real_http;

	public $Parent;
	public $Services;

	private $environment;



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
	 * final public function __construct ([Object $Parent])
	 *
	 * Returns none
	 */
	final public function __construct ($Parent = null) {
		$this->Parent = $Parent;

		$Class = new \ReflectionClass($this);

		$this->name = end(explode('\\', $Class->getNameSpaceName()));
		$this->path = dirname($Class->getFileName()).'/';
		$this->http = BASE_HTTP.strtolower($this->name).'/';
		$this->real_http = BASE_HTTP.preg_replace('|^'.BASE_PATH.'|i', '', $this->path);

		$this->Services = new Services;

		$this->Services->register('Config', 'Fol\\Config', array($this->path.'config/'));
		$this->Services->register('Views', 'Fol\\Views');
		$this->Services->register('Models', 'Fol\\Models');
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
	 * public function execute (Fol\Request $Request)
	 *
	 * Executes the controller of the application
	 * Returns none
	 */
	public function execute (Request $Request) {
		$controllers_config = $this->Config->get('controllers');

		try {
			$controller = Router::getController($this->name, $Request, $controllers_config);

			if ($controller) {
				list($class, $method, $parameters, $path) = $controller;

				$Request->Path->set($path);

				$controller = new $class($this, $Request);

				$Response = call_user_func_array(array($controller, $method), $parameters);
			} else {
				throw new HttpException(Response::$status[404], 404);
			}
		}

		catch (HttpException $Exception) {
			$Response = $this->executeException($Exception, $controllers_config['HttpException']);
		}

		catch (\ErrorException $Exception) {
			$Response = $this->executeException($Exception, $controllers_config['ErrorException']);
		}

		if (!($Response instanceof Response)) {
			$Response = new Response($Response);
		}

		return $Response;
	}


	
	/**
	 * private function executeException ($Exception, $controller)
	 *
	 * Executes the controller of the exception application
	 * Returns none
	 */
	private function executeException ($Exception, $controller) {
		if ($controller = Router::getExceptionController($this->name, $Exception, $controller)) {
			list($class, $method) = $controller;

			$controller = new $class($this);
			$Response = $controller->$method($Exception);

			if (!is_object($Response) || (get_class($Response) !== 'Fol\\Response')) {
				return new Response($Response);
			}

			return $Response;
		}
		
		return new Response($Exception->getMessage(), $Exception->getCode());
	}
}
?>