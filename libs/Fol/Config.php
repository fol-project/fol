<?php
/**
 * Fol\Config
 * 
 * This is a simple class to load configuration data from php files
 * You must define a base folder and the class search for the files inside automatically.
 * 
 * The class will search the database configuration in this two files.
 */
namespace Fol;

class Config {
	protected $configPaths = [];
	protected $items = [];


	/**
	 * Constructor method. You must define the base folder where the config files are stored
	 * 
	 * @param string/array $paths The base folder paths
	 */
	public function __construct ($paths) {
		$this->addFolders($paths);
	}


	/**
	 * Adds new base folders where search for the config files
	 * 
	 * @param string/array $paths The base folder paths
	 * @param boolean $prepend If it's true, insert the new folder at begining of the array.
	 */
	public function addFolders ($paths, $prepend = true) {
		$paths = (array)$paths;

		foreach ($paths as &$path) {
			if (substr($path, -1) !== '/') {
				$path .= '/';
			}
		}

		if ($prepend === true) {
			$this->configPaths = array_merge($paths, $this->configPaths);
		} else {
			$this->configPaths = array_merge($this->configPaths, $paths);
		}
	}



	/**
	 * Magic function to convert all data loaded in a string (for debug purposes)
	 *
	 * echo (string)$data;
	 */
	public function __toString () {
		$text = '';

		foreach ($this->items as $name => $value) {
			if (is_array($value)) {
				$value = json_encode($value);
			}

			$text .= "$name: $value\n";
		}

		return $text;
	}



	/**
	 * Read data from php file (that returns the value)
	 * 
	 * @param string $name The name of the data (must be the name of the files where the data are stored)
	 * 
	 * @return mixed The data or null if doesn't exists
	 */
	public function read ($name) {
		if (substr($name, -4) !== '.php') {
			$name .= '.php';
		}

		foreach ($this->configPaths as $path) {
			if (is_file($path.$name)) {
				return include($path.$name);
			}
		}
	}



	/**
	 * Gets the data. Loads automatically the data if it has not been loaded.
	 * If no name is defined, returns all loaded data
	 *
	 * @param $name The name of the data
	 * 
	 * @return mixed The data or null
	 */
	public function get ($name = null) {
		if (func_num_args() === 0) {
			return $this->items;
		}

		if (!isset($this->items[$name])) {
			$this->items[$name] = $this->read($name);
		}

		return $this->items[$name];
	}



	/**
	 * Sets a new value
	 * 
	 * $data->set('database', array(
	 *     'host' => 'localhost',
	 *     'database' => 'my-database',
	 *     'user' => 'admin',
	 *     'password' => '1234',
	 * ));
	 * 
	 * You can use an array directly to store more than one data:
	 * 
	 * $data->set(array(
	 * 	   'database' => array(
	 *         'host' => 'localhost',
	 *         'database' => 'my-database',
	 *         'user' => 'admin',
	 *         'password' => '1234'
	 *     ),
	 *     'database2' => array(
	 *         'host' => 'localhost',
	 *         'database' => 'my-database',
	 *         'user' => 'admin',
	 *         'password' => '1234'
	 *     ),
	 * ));
	 * 
	 * @param string $name The data name or an array with all data name and value
	 * @param array $value The value of the data
	 */
	public function set ($name, array $value = null) {
		if (is_array($name)) {
			$this->items = array_replace($this->items, $name);
		} else {
			$this->items[$name] = $value;
		}
	}


	
	/**
	 * Deletes a data value
	 * 
	 * $data->delete('database');
	 * 
	 * @param string $name The name of the data
	 */
	public function delete ($name, $key = null) {
		unset($this->items[$name]);
	}
}
