<?php
/**
 * Fol\Config
 * 
 * This is a simple class to load configuration data from php files
 * You must define a base folder and the class search for the files inside automatically.
 * 
 * Example:
 * $Config->get('database');
 * 
 * config_folder/database.php
 * 
 * You can define an environment (for example localhost) to load different data in different environments:
 * 
 * $Config->environment = 'localhost';
 * $Config->get('database');
 * 
 * config_folder/localhost/database.php
 * config_folder/database.php
 * 
 * The class will search the database configuration in this two files. If the first file is found, stops and don't search in the second.
 */
namespace Fol;

class Config {
	public $environment;

	private $folder;
	private $items = array();


	/**
	 * Constructor. You can define the folder where search the data
	 *
	 * $data = new Fol\Config('apps/my_app/config')
	 * 
	 * @param string $folder The folder where the data files are placed.
	 */
	public function __construct ($folder = null) {
		if ($folder !== null) {
			$this->setFolder($folder);
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
	 * Define the base folder where all files will be searched
	 *
	 * @param string $folder The folder path
	 * 
	 * @return true if the folder is valid. If it's not valid, throws an ErrorException.
	 */
	public function setFolder ($folder) {
		if (is_dir($folder)) {
			if ($folder[strlen($folder) - 1] !== '/') {
				$folder .= '/';
			}

			$this->folder = $folder;

			return true;
		}

		throw new \ErrorException('The folder "'.$folder.'" does not exists');
	}



	/**
	 * Returns the current base folder
	 *
	 * @return string The folder path
	 */
	public function getFolder () {
		return $this->folder;
	}



	/**
	 * Read data from php file (that returns the value)
	 * 
	 * @param string $name The name of the data (must be the name of the files where the data are stored)
	 * 
	 * @return mixed The data or null if doesn't exists
	 */
	public function read ($name) {
		if (!$this->folder) {
			throw new \ErrorException('The base folder is not defined');

			return false;
		}

		if ($this->environment && is_file($this->folder.$this->environment.'/'.$name.'.php')) {
			$data = include($this->folder.$this->environment.'/'.$name.'.php');
		} else if (is_file($this->folder.$name.'.php')) {
			$data = include($this->folder.$name.'.php');
		} else {
			$data = null;
		}

		return $data;
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
	 * Merges the old values with new values
	 * 
	 * @param string $name The data name or an array with all data name and value
	 * @param array $value The value of the data
	 */
	public function merge ($name, array $value = null) {
		if (!isset($this->items[$name])) {
			$this->items[$name] = $value;
		} else if (is_array($name)) {
			$this->items = array_replace_recursive($this->items, $name);
		} else if ($value !== null) {
			$this->items[$name] = array_replace_recursive($this->items[$name], $value);
		}
	}



	/**
	 * Deletes a data value
	 * 
	 * $data->delete('database');
	 * 
	 * or if you want delete just one sub-value
	 * 
	 * $data->delete('database', 'user');
	 *
	 * @param string $name The name of the data
	 * @param string $key The optional key of the data
	 */
	public function delete ($name, $key = null) {
		if ($key === null) {
			unset($this->items[$name]);
		} else {
			unset($this->items[$name][$key]);

			if (!$this->items[$name]) {
				unset($this->items[$name]);
			}
		}
	}



	/**
	 * Checks if some data exists
	 * 
	 * $data->has('database');
	 * 
	 * or if you want to check just one sub-value
	 * 
	 * $data->has('database', 'user');
	 * 
	 * @param string $name The name of the data
	 * @param string $key The optional key of the data
	 * 
	 * @return boolean True if the data exists, false if not.
	 */
	public function has ($name, $key = null) {
		if (!array_key_exists($name, $this->items)) {
			return false;
		}

		return ($key === null) ? true : array_key_exists($key, $this->items[$name]);
	}
}
?>
