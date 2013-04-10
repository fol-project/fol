<?php
/**
 * Fol\App
 * 
 * This is the abstract class that all apps must extend. Provides the basic functionality parameters (paths, urls, namespace, parent, etc)
 */

namespace Fol;

abstract class App {
	public $Parent;


	public static function buildCliOptions (array $arguments) {
		$options = [
			'num' => [],
			'name' => []
		];

		while ($arguments) {
			$option = array_shift($arguments);

			if (preg_match('#^(-+)([\w]+)$#', $option, $match)) {
				$option = $match[2];
				$options['name'][$option] = $arguments ? array_shift($arguments) : true;
			} else {
				$options['num'][] = $option;
			}
		}

		return $options;
	}


	/**
	 * Executes a method
	 * 
	 * @param string $method The method name
	 * @param array $arguments The variables passed to the method.
	 */
	public function invoke ($method, array $arguments) {
		if (method_exists($this, $method)) {
			$options = static::buildCliOptions($arguments);

			$Method = new \ReflectionMethod($this, $method);

			$arguments = array();

			foreach ($Method->getParameters() as $Parameter) {
				if (isset($options['name'][$Parameter->getName()])) {
					$arguments[] = $options['name'][$Parameter->getName()];
				} else if ($options['num']) {
					$arguments[] = array_shift($options['num']);
				} else if ($Parameter->isDefaultValueAvailable()) {
					$arguments[] = $Parameter->getDefaultValue();
				}
			}

			$Method->invokeArgs($this, $arguments);
		}
	}


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

		//The app path. (/sites/my-site/web)
		if ($name === 'path') {
			return $this->path = str_replace('\\', '/', dirname((new \ReflectionClass($this))->getFileName()));
		}

		//The app base url
		if ($name === 'url') {
			return $this->url = '';
		}

		//The assets app path. (/sites/my-site/web/assets)
		if ($name === 'assetsPath') {
			return $this->assetsPath = $this->path.'/assets';
		}

		//The assets app url (/web/assets)
		if ($name === 'assetsUrl') {
			return $this->assetsUrl = BASE_URL.preg_replace('|^'.BASE_PATH.'|', '', $this->path).'/assets';
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
