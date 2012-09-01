<?php
/**
 * Fol\Loader
 * 
 * A class to autoload all classes using the PSR-0 standard
 */
namespace Fol;

class Loader {
	static private $libraries_path;
	static private $classes = array();
	static private $namespaces = array();


	/**
	 * Sets the base path where the libraries are stored
	 * 
	 * Example:
	 * Fol\Loader::setLibrariesPath('my_web/vendor')
	 * 
	 * @param string $libraries_path The path of the folder where the libraries are stored
	 * 
	 * @throws an ErrorException if the folder does not exists
	 */
	static public function setLibrariesPath ($libraries_path) {
		if (is_dir($libraries_path)) {
			if ($libraries_path[strlen($libraries_path) - 1] !== '/') {
				$libraries_path .= '/';
			}

			self::$libraries_path = $libraries_path;
		} else {
			throw new \ErrorException("The folder '$libraries_path' does not exists");
		}
	}



	/**
	 * Installs this class loader on the SPL autoload stack.
	 */
	static public function register () {
		spl_autoload_register(__NAMESPACE__.'\\Loader::autoload');
	}


	/**
	 * Uninstalls this class loader from the SPL autoloader stack.
	 */
	static public function unregister () {
		spl_autoload_unregister(__NAMESPACE__.'\\Loader::autoload');
	}



	/**
	 * Basic autoload function. Executed automatically when a class needs to be loaded.
	 * 
	 * @param string $class_name The class to be loaded (for example: Fol\Http\Request)
	 */
	static public function autoload ($class_name) {
		$file = self::getFile($class_name);

		if ($file && is_readable($file)) {
			include_once($file);
		}
	}



	/**
	 * Returns the path of a class
	 * 
	 * @param string $class_name The class name (for example: Fol\Http\Request)
	 * 
	 * @return string The file path of the class
	 */
	static public function getFile ($class_name) {
		$class_name = ltrim($class_name, '\\');

		if (isset(self::$classes[$class_name])) {
			return self::$classes[$class_name];
		}

		$namespace = '';

		if (($last_pos = strripos($class_name, '\\')) !== false) {
			$namespace = substr($class_name, 0, $last_pos);
			$class_name = substr($class_name, $last_pos + 1);
		}

		foreach (self::$namespaces as $ns => $path) {
			if (strpos($namespace, $ns) === 0) {
				return self::filePath(preg_replace('#^'.$ns.'#', '', $namespace), $class_name, $path);
			}
		}

		return self::filePath($namespace, $class_name);
	}



	/**
	 * Private function to generate the file path of a class.
	 * 
	 * @param string $namespace The namespace of the class (for example: Fol\Http)
	 * @param string $class_name The name of the class (for example: Request)
	 * @param string $libraries_path The custom libraries path. If it's not defined, uses the default libraries path
	 * 
	 * @return string The file path
	 * 
	 */
	static private function filePath ($namespace, $class_name, $libraries_path = null) {
		$file = isset($libraries_path) ? $libraries_path : self::$libraries_path;

		if (!empty($namespace)) {
			$file .= '/'.str_replace('\\', '/', $namespace);
		}

		return $file.'/'.str_replace('_', '/', $class_name).'.php';
	}



	/**
	 * Sets a custom path for an specific class.
	 * 
	 * @param string $class The class name
	 * @param string $path The custom path for this class
	 */
	static public function registerClass ($class, $path = null) {
		if (is_array($class)) {
			foreach ($class as $class => $path) {
				self::$classes[$class] = $path;
			}

			return;
		}

		self::$classes[$class] = $path;
	}



	/**
	 * Sets a new base path for an specific namespace
	 * 
	 * @param string $namespace The namespace to register (for example Fol\Http). You can define also an array of namespace => path values
	 * @param string $path The custom path for this namespace.
	 */
	static public function registerNamespace ($namespace, $path = null) {
		if (is_array($namespace)) {
			foreach ($namespace as $namespace => $path) {
				self::$namespaces[$namespace] = $path;
			}

			return;
		}

		self::$namespaces[$namespace] = $path;
	}



	/**
	 * Register the classes installed by composer.
	 * Search in the libraries path for the composer directory and loads the classmap and namespaces registered.
	 */
	static function registerComposer () {
		$file = self::$libraries_path.'composer/autoload_classmap.php';

		if (is_file($file)) {
			self::registerClass(include($file));
		}

		$file = self::$libraries_path.'composer/autoload_namespaces.php';

		if (is_file($file)) {
			foreach (include($file) as $namespace => $path) {
				self::registerNamespace($namespace, $path.$namespace.'/');
			}
		}
	}
}
?>
