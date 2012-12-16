<?php
/**
 * Fol\Utils\FileRouter
 * 
 * Provides a simple router handler for preprocessed files in Apps
 */
namespace Fol\Utils;

use Fol\Http\Headers;
use Fol\Http\Request;
use Fol\Http\Response;
use Fol\Http\Router as HttpRouter;
use Fol\Http\HttpException;

trait FileRouter {

	/**
	 * Handle a http request
	 * 
	 * $app->handle($Request);
	 * 
	 * @param Fol\Http\Request $Request The request object used or the path of the request
	 * 
	 * @return Fol\Http\Response The response object with the controller result
	 */
	public function handleFile ($Request = null) {
		if (func_num_args() === 0) {
			$Request = Request::createFromGlobals();
		} else if (!is_object($Request) || get_class($Request) !== 'Fol\\Http\\Request') {
			$Request = Request::create($Request);
		}

		$file = preg_replace('#^'.preg_quote($this->assetsUrl.'cache/', '#').'#', '', $Request->getUrl(false));
		$controller = HttpRouter::checkController($Request, $this->namespace.'\\Controllers\\Files', $Request->getFormat(), array($file));

		try {
			if ($controller === false) {
				throw new HttpException('File cannot be preprocessed', 500);
			} else {
				$Response = HttpRouter::executeController($controller, array('App' => $this, 'Request' => $Request));
			}
		} catch (HttpException $Exception) {
			$Response = new Response($Exception->getMessage(), $Exception->getCode());
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

		if (is_dir($path) === true) {
			$files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);

			foreach ($files as $file) {
				if ($file->isDir() === true) {
					rmdir($file->getPathName());
				} else if (($file->isFile() === true) || ($file->isLink() === true)) {
					unlink($file->getPathname());
				}
			}

			return rmdir($path);
		}

		if ((is_file($path) === true) || (is_link($path) === true)) {
			return unlink($path);
		}

		return false;
	}
}
?>
