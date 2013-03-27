<?php
/**
 * Fol\Http\CacheFileContainer
 * 
 * Simple class used to store cache responses in files
 */
namespace Fol\Http;

class CacheFileContainer implements ContainerInterface {
	protected $path;
	protected $items = array();

	
	public function __construct ($path) {
		$this->path = $path;
	}


	public function getCacheFilename ($name) {
		return $this->path."/$name.php";
	}


	/**
	 * Gets one or all parameters.
	 * 
	 * $params->get() Returns all parameters
	 * $params->get('name') Returns just this parameter
	 * 
	 * @param string $name The parameter name
	 * @param mixed $default The default value if the parameter is not set
	 * 
	 * @return mixed The parameter value or the default
	 */
	public function get ($name = null, $default = null) {
		$file = $this->getCacheFilename($name);

		if (is_file($file)) {
			return include($file);
		}

		return $default;
	}



	/**
	 * Sets one parameter or various new parameters
	 * 
	 * @param string $name The parameter name. You can define an array with name => value to insert various parameters
	 * @param mixed $value The parameter value.
	 */
	public function set ($name, $value = null) {
		$value = '<?php return '.var_export($value, true).'; ?>';

		file_put_contents($this->getCacheFilename($name), $value);
	}



	/**
	 * Deletes one or all parameters
	 * 
	 * $params->delete('name') Deletes one parameter
	 * $params->delete() Deletes all parameter
	 * 
	 * @param string $name The parameter name
	 */
	public function delete ($name = null) {
		$file = $this->getCacheFilename($name);

		if (is_file($file)) {
			unlink($file);
		}
	}



	/**
	 * Checks if a parameter exists
	 * 
	 * @param string $name The parameter name
	 * 
	 * @return boolean True if the parameter exists (even if it's null) or false if not
	 */
	public function has ($name) {
		return is_file($this->getCacheFilename($name));
	}
}
?>