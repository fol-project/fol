<?php
namespace Fol;

class Loader {
	static $libraries_path;

	static $classes = array();
	static $prefixes = array();
	static $namespaces = array();
	static $namespaces_paths = array();



	/**
	 * static public function register ()
	 *
	 * Installs this class loader on the SPL autoload stack.
	 */
	static public function register () {
		self::$libraries_path = self::$libraries_path ?: BASE_PATH.'libraries/';

		spl_autoload_register(array(self, 'autoload'));
	}


	/**
	 * static public function unregister ()
	 *
	 * Uninstalls this class loader from the SPL autoloader stack.
	 */
	static public function unregister () {
		spl_autoload_unregister(array(self, 'autoload'));
	}



	/**
	 * static private function autoload ($class_name)
	 *
	 * Basic autoload function
	 * Returns boolean
	 */
	static private function autoload ($class_name) {
		if ($file = self::getFile($class_name)) {
			include_once($file);
		}
	}



	/**
	 * static public function getFile ($class_name)
	 *
	 * Find a class file
	 * Returns string/false
	 */
	static public function getFile ($class_name) {
		$class_name = ltrim($class_name, '\\');

		if (isset(self::$classes[$class_name])) {
			$file = self::$classes[$class_name];

			if (is_file($file)) {
				return $file;
			}

			return false;
		}

		$namespace = '';

		if ($last_pos = strripos($class_name, '\\')) {
			$namespace = substr($class_name, 0, $last_pos);
			$class_name = substr($class_name, $last_pos + 1);
		}

		foreach (array_keys(self::$namespaces) as $ns) {
			if (strpos($namespace, $ns) === 0) {
				if ($file = self::filePath(self::$namespaces_paths[$ns], preg_replace('#^'.$ns.'#', '', $namespace), $class_name)) {
					return $file;
				}

				break;
			}
		}

		foreach (self::$prefixes as $prefix => $path) {
			if (strpos($class_name, $prefix) === 0) {
				if ($file = self::filePath($path, $namespace, $class_name)) {
					return $file;
				}

				break;
			}
		}

		return self::filePath(self::$libraries_path, $namespace, $class_name);
	}



	/**
	 * static private function filePath (string $base_path, string $namespace, string $class_name)
	 *
	 * Generate the filename and check if it exists
	 * Returns string/boolean
	 */
	static private function filePath ($base_path, $namespace, $class_name) {
		$file = $base_path;

		if ($namespace) {
			$file .= str_replace('\\', DIRECTORY_SEPARATOR, $namespace).DIRECTORY_SEPARATOR;
		}

		$file .= str_replace('_', DIRECTORY_SEPARATOR, $class_name).'.php';

		if (is_file($file)) {
			return $file;
		}

		return false;
	}



	/**
	 * static public function registerNamespace (array $namespaces)
	 * static public function registerNamespace (string $namespace, string $path)
	 *
	 * Sets a new base path for an specific namespace
	 * Returns none
	 */
	static public function registerNamespace ($namespace, $path = null) {
		if (is_array($namespace)) {
			foreach ($namespace as $key => $value) {
				self::registerNamespace($key, $value);
			}

			return;
		}

		if (!isset(self::$namespaces[$namespace])) {
			self::$namespaces[$namespace] = substr_count($namespace, '\\');

			arsort(self::$namespaces, SORT_NUMERIC);
		}

		self::$namespaces_paths[$namespace] = $path;
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
				self::registerClass($key, $value);
			}

			return;
		}

		self::$classes[$class] = $path;
	}



	/**
	 * public function registerPrefix (array $prefixes)
	 * public function registerPrefix (string $prefix, string $path)
	 *
	 * Sets a new path for an specific prefix in class name
	 * Returns none
	 */
	public function registerPrefix ($prefix, $path = null) {
		if (is_array($prefix)) {
			foreach ($prefix as $key => $value) {
				self::registerPrefix($key, $value);
			}

			return;
		}

		self::$prefixes[$prefix] = $path;
	}
}
?>