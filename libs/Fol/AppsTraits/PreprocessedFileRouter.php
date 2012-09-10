<?php
/**
 * Fol\AppsTraits\PreprocessedFileRouter
 * 
 * Provides a simple router handler for preprocessed files
 */
namespace Fol\AppsTraits;

use Fol\Http\Headers;
use Fol\Http\Request;
use Fol\Http\Response;
use Fol\Http\Router;
use Fol\Http\HttpException;

trait PreprocessedFileRouter {

	/**
	 * Handle a http request
	 * 
	 * $app->handle($Request);
	 * 
	 * @param Fol\Http\Request $Request The request object used or the path of the request
	 * 
	 * @return Fol\Http\Response The response object with the controller result
	 */
	public function handleFile (Request $Request) {
		$file = preg_replace('#^'.preg_quote($this->assetsUrl.'cache/', '#').'#', '', $Request->getFullPath(true));
		$class = $this->namespace.'\\Controllers\\Files';

		if (class_exists($class)) {
			$controller = Router::checkControllerMethod($Request, new \ReflectionClass($class), $Request->getFormat(), array($file));

			try {
				if ($controller === false) {
					$Response = new Response('File cannot be preprocessed', 404);
				} else {
					$Response = Router::executeController($controller[0], $controller[1], array($this, $Request));
				}
			} catch (\Exception $Exception) {
				$Response = new Response($Exception->getMessage(), $Exception->getCode());
			}
		} else {
			$Response = new Response('Controllers not defined', 404);
		}

		return $Response;
	}

	
	/**
	 * Returns the path where a cached file is stored. Creates also the directories if it's needle
	 * 
	 * @param string $file The file path (from the assets folder)
	 * 
	 * @return string The file path
	 */
	public function getCacheFilePath ($file) {
		$path = dirname($file);

		if (!is_dir($this->assetsPath.'cache/'.$path)) {
			mkdir($this->assetsPath.'cache/'.$path, 0777, true);
		}

		return $this->assetsPath.'cache/'.$file;
	}


	/**
	 * Removes cached files and folders
	 * 
	 * @param string $path The file/folder path
	 * 
	 * @return boolean True if everything has removed, false if there was errors
	 */
	public function removeCache ($path) {
		$path = $this->getCacheFilePath($path);

		if (is_dir($path)) {
			$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path), \RecursiveIteratorIterator::CHILD_FIRST);

			foreach ($iterator as $path) {
				if ($path->isDir()) {
					if (rmdir($path->__toString()) === false) {
						return false;
					}
				} else {
					if (unlink($path->__toString()) === false) {
						return false;
					}
				}
			}

			return true;
		}

		if (is_file($path)) {
			return unlink($path);
		}

		return false;
	}
}
?>
