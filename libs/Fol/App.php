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
		if ($name === 'name') {
			return $this->name = substr(strrchr($this->namespace, '\\'), 1);
		}

		if ($name === 'namespace') {
			return $this->namespace = (new \ReflectionClass($this))->getNameSpaceName();
		}

		if ($name === 'path') {
			return $this->path = str_replace('\\', '/', dirname((new \ReflectionClass($this))->getFileName())).'/';
		}

		if ($name === 'url') {
			return $this->url = BASE_URL;
		}

		if ($name === 'assetsPath') {
			return $this->assetsPath = BASE_PATH.'assets/';
		}

		if ($name === 'assetsUrl') {
			return $this->assetsUrl = BASE_URL.'assets/';
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
