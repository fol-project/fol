<?php
namespace Fol;

class Loader {
	private $namespaces = array();
	private $classes = array();



	/**
	 * public function __construct ()
	 *
	 * Returns none
	 */
	public function __construct () {
		spl_autoload_register(array($this, 'autoload'));
	}



	/**
	 * private function autoload ($class_name)
	 *
	 * Basic autoload function
	 * Returns none
	 */
	private function autoload ($class_name) {
		if (isset($this->classes[$class_name])) {
			$file = $this->classes[$class_name];

			if (is_file($file)) {
				include_once($file);
			}

			return;
		}

		$file = explode('\\', $class_name);

		if (isset($this->namespaces[$file[0]])) {
			$path = $this->namespaces[array_shift($file)];

			$file = $path.implode('/', $file).'.php';

			if (is_file($file)) {
				include_once($file);
			}

			return;
		}

		$file = BASE_PATH.'libraries/'.implode('/', $file).'.php';

		if (is_file($file)) {
			include_once($file);
		}
	}



	/**
	 * public function registerNamespace (array $namespaces)
	 * public function registerNamespace (string $namespace, string $path)
	 *
	 * Sets a new base path for an specific namespace
	 * Returns none
	 */
	public function registerNamespace ($namespace, $path = null) {
		if (is_array($namespace)) {
			foreach ($namespace as $key => $value) {
				$this->namespaces[$key] = $value;
			}

			return;
		}

		$this->namespaces[$namespace] = $path;
	}



	/**
	 * public function registerClass (array $classes)
	 * public function registerClass (string $class, string $path)
	 *
	 * Sets a new path for an specific class
	 * Returns none
	 */
	public function registerClass ($class, $path = null) {
		if (is_array($class)) {
			foreach ($class as $key => $value) {
				$this->classes[$key] = $value;
			}

			return;
		}

		$this->classes[$class] = $path;
	}
}
?>