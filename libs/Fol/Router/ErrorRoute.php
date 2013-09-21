<?php
/**
 * Fol\Router\ErrorRoute
 * 
 * Class to manage an error route
 * Based in PHP-Router library (https://github.com/dannyvankooten/PHP-Router) and Aura-PHP.Router (https://github.com/auraphp/Aura.Router)
 */
namespace Fol\Router;

use Fol\Http\Request;
use Fol\Http\Response;
use Fol\Http\HttpException;

class ErrorRoute {
	private $target;

	public function __construct ($target) {
		$this->target = $target;
	}

	public function execute ($app, $exception, $request) {
		ob_start();

		$return = '';
		$response = $request->generateResponse();
		$response->setStatus($exception->getCode() ?: 500);

		list($class, $method) = $this->target;

		$class = new \ReflectionClass($class);
		$controller = $class->newInstanceWithoutConstructor();
		$controller->app = $app;
		$controller->route = $this;

		if (($constructor = $class->getConstructor())) {
			$constructor->invoke($controller);
		}

		$request->parameters->set('exception', $exception);
		$return = $class->getMethod($method)->invoke($controller, $request, $response);
		
		if ($return instanceof Response) {
			$return->appendContent(ob_get_clean());
			
			return $return;
		}

		$response->appendContent(ob_get_clean().$return);

		return $response;
	}
}
