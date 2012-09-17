<?php
/**
 * Fol\AppsTraits\SymfonyRouter
 * 
 * Provides a Symfony based router handler.
 * Based on this article from Fabien Potencier:
 * http://fabien.potencier.org/article/50/create-your-own-framework-on-top-of-the-symfony2-components-part-1
 */
namespace Fol\AppsTraits;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RouteCollection;

use Symfony\Component\HttpKernel\Controller\ControllerResolver;

trait SymfonyRouter {

	/**
	 * Handle a http request
	 * 
	 * $app->handle($Request);
	 * $app->handle('my/path', 'POST', array('name' => 'value'));
	 * 
	 * @param Symfony\Component\HttpFoundation\Request $Request The request object used or the path of the request
	 * @param string $method The method used (if we defined a path as $Request)
	 * @param array $parameters The variables of the request (if we defined a path as $Request)
	 * 
	 * @return Symfony\Component\HttpFoundation\Response The response object with the controller result
	 */
	public function handle ($Request = null, $method = 'GET', array $parameters = array()) {
		if (func_num_args() === 0) {
			$Request = Request::createFromGlobals();
		} else if (!is_object($Request) || get_class($Request) !== 'Symfony\\Component\\HttpFoundation\\Request') {
			$Request = Request::create($Request, $method, $parameters);
		}

		$Context = new RequestContext();
		$Context->fromRequest($Request);
		$Matcher = new UrlMatcher($this->Routes, $Context);
		$Resolver = new ControllerResolver();

		try {
			$Request->attributes->add($Matcher->match($Request->getPathInfo()));

			$controller = $Resolver->getController($Request);
			$arguments = $Resolver->getArguments($Request, $controller);

			$Response = call_user_func_array($controller, $arguments);
		} catch (ResourceNotFoundException $Exception) {
			$Response = new Response('Not Found', 404);
		} catch (\Exception $Exception) {
			$Response = new Response('An error occurred', 500);
		}

		if (!is_string($Response)) {
			$Response = new Response($Response);
		}

		return $Response;
	}


	public function addRoutes (array $routes) {
		if (!isset($this->Routes)) {
			$this->Routes = new RouteCollection;
		}

		foreach ($routes as $name => $route) {
			$this->Routes->add($name, $route);
		}
	}
}
?>
