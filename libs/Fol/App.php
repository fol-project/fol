<?php
/**
 * Fol\App
 * 
 * This is the abstract class that all apps must extend. Provides the basic functionality parameters (paths, urls, namespace, parent, etc)
 */

namespace Fol;

abstract class App {
	public $Parent;


	/**
	 * Magic function to get some special properties.
	 * Instead calculate this on the __constructor, is better use __get to do not obligate to call this constructor in the extensions of this class
	 * 
	 * @param string $name The name of the property
	 * 
	 * @return string The property value or null
	 */
	public function __get ($name) {
		//The app name. (Web)
		if ($name === 'name') {
			return $this->name = substr(strrchr($this->namespace, '\\'), 1);
		}

		//The app namespace. (Apps\Web)
		if ($name === 'namespace') {
			return $this->namespace = (new \ReflectionClass($this))->getNameSpaceName();
		}

		//The app path. (/sites/my-site/web/)
		if ($name === 'path') {
			return $this->path = str_replace('\\', '/', dirname((new \ReflectionClass($this))->getFileName())).'/';
		}

		//The app base url (/)
		if ($name === 'url') {
			return $this->url = BASE_URL;
		}

		//The assets app path. (/sites/my-site/web/assets/)
		if ($name === 'assetsPath') {
			return $this->assetsPath = $this->path.'assets/';
		}

		//The assets app url (/web/assets/)
		if ($name === 'assets') {
			return $this->assetsUrl = BASE_URL.preg_replace('|^'.BASE_PATH.'|', '', $this->path).'assets/';
		}

		//The assets library (/assets/)
		if ($name === 'assetsLibs') {
			return BASE_URL.'assets/';
		}
	}


	/**
	 * Define a Parent property (the app that contain this app)
	 * 
	 * @param Fol\App $Parent An App instance
	 */
	public function setParent (App $Parent) {
		$this->Parent = $Parent;
	}
}
?>
