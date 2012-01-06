<?php
namespace Fol;

class Loader {
	public $default_base_path;

	private $classes = array();
	private $namespaces = array();
	private $namespaces_paths = array();



	/**
	 * public function __construct ()
	 *
	 * Returns none
	 */
	public function __construct () {
		$this->default_base_path = BASE_PATH.'libraries/';
		$this->register();
	}


	/**
	 * Installs this class loader on the SPL autoload stack.
	 */
	public function register () {
		spl_autoload_register(array($this, 'autoload'));
	}


	/**
	 * Uninstalls this class loader from the SPL autoloader stack.
	 */
    public function unregister () {
		spl_autoload_unregister(array($this, 'autoload'));
	}



	/**
	 * private function autoload ($class_name)
	 *
	 * Basic autoload function
	 * Returns none
	 */
	private function autoload ($class_name) {
		$class_name = ltrim($class_name, '\\');

		if (isset($this->classes[$class_name])) {
			$file = $this->classes[$class_name];

			if (is_file($file)) {
				include_once($file);
			}

			return;
		}

		$namespace = '';

		if ($last_pos = strripos($class_name, '\\')) {
			$namespace = substr($class_name, 0, $last_pos);
			$class_name = substr($class_name, $last_pos + 1);
		}

		$base_path = $this->default_base_path;

		foreach (array_keys($this->namespaces) as $ns) {
			if (strpos($namespace, $ns) === 0) {
				$base_path = $this->namespaces_paths[$ns];
				$namespace = preg_replace('#^'.$ns.'#', '', $namespace);

				break;
			}
		}

		$file = $base_path.str_replace('\\', DIRECTORY_SEPARATOR, $namespace).DIRECTORY_SEPARATOR;
		$file .= str_replace('_', DIRECTORY_SEPARATOR, $class_name).'.php';

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
				$this->registerNamespace($key, $value);
			}

			return;
		}

		if (!isset($this->namespaces[$namespace])) {
			$this->namespaces[$namespace] = substr_count($namespace, '\\');

			arsort($this->namespaces, SORT_NUMERIC);
		}

		$this->namespaces_paths[$namespace] = $path;
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