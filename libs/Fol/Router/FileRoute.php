<?php
/**
 * Fol\Router\FileRoute
 * 
 * Class to manage a route to a file
 * Based in PHP-Router library (https://github.com/dannyvankooten/PHP-Router) and Aura-PHP.Router (https://github.com/auraphp/Aura.Router)
 */
namespace Fol\Router;

use Fol\Http\Request;
use Fol\Http\Response;
use Fol\Http\HttpException;

class FileRoute {
	private $path;
	private $target;

	public function __construct ($path, $target) {
		$this->path = $path;
		$this->target = $target;
	}

	public function getType () {
		return 'file';
	}

	public function getPath () {
		return $this->path;
	}

	public function getTarget () {
		return $this->target;
	}

	public function match ($request) {
		return (strpos($request->getPath(true), $this->path) === 0);
	}

	public function execute ($app, $request) {
		ob_start();

		$return = '';
		$response = $request->generateResponse();

		$request->parameters->set('file', substr($request->getPath(true), strlen($this->path)));

		try {
			list($class, $method) = $this->target;

			$class = new \ReflectionClass($class);
			$controller = $class->newInstanceWithoutConstructor();
			$controller->app = $app;
			$controller->route = $this;

			if (($constructor = $class->getConstructor())) {
				$constructor->invoke($controller, $request, $response);
			}

			if ($method) {
				$return = $class->getMethod($method)->invoke($controller, $request, $response);
			} else {
				$return = $controller($request, $response);
			}

			unset($controller);
		} catch (\Exception $exception) {
			ob_clean();

			if (!($exception instanceof HttpException)) {
				$exception = new HttpException('Error processing request', 500, $exception);
			}

			throw $exception;
		}

		if ($return instanceof Response) {
			$return->appendContent(ob_get_clean());
			
			return $return;
		}

		$response->appendContent(ob_get_clean().$return);

		return $response;
	}
}
