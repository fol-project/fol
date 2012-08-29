<?php
/**
 * Fol\Config
 * 
 * This is a simple class to load any configuration data from an php file (that returns an array) or an .ini format
 * You must define a base folder and the class search for the configuration files inside automatically.
 * 
 * Example:
 * $config->get('database');
 * 
 * config_folder/database.php
 * config_folder/database.ini
 * 
 * You can define an enviroment (for example localhost) to load different configuration in different enviroments:
 * 
 * $config->enviroment = 'localhost';
 * $config->get('database');
 * 
 * config_folder/localhost/database.php
 * config_folder/localhost/database.ini
 * config_folder/database.php
 * config_folder/database.ini
 * 
 * The class will search the database config data in this four files. When a file is found, stops and don't search in the remainings.
 */
namespace Fol;

class Config {
	public $environment;

	private $folder;
	private $items = array();


	/**
	 * Constructor. You can define the folder where search the configuration
	 *
	 * $config = new Fol\Config('apps/my_app/config')
	 */
	public function __construct ($folder = null) {
		$this->setFolder($folder);
	}



	/**
	 * Magic function to convert all configuration data loaded in a string (for debug purposes)
	 *
	 * echo (string)$config;
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
	 * Define the base folder where all config files will be searched
	 *
	 * @param string $folder The folder path
	 * 
	 * @return true if the folder is valid. If it's not valid, throws an ErrorException.
	 */
	public function setFolder ($folder) {
		if (is_dir($folder)) {
			$this->folder = $folder;

			return true;
		}

		throw new ErrorException('The folder config "'.$folder.'" does not exists');
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
	 * Loads the data of config files (in php or ini format)
	 * 
	 * @param string $name The name of the configuration (must be the name of the files where the data are stored)
	 * 
	 * @return mixed The configuration data or null if doesn't exists
	 */
	public function load ($name) {
		if (!$this->folder) {
			throw new ErrorException('The folder config is not set');

			return false;
		}

		$file = $name.'.php';

		if ($this->environment && file_exists($this->folder.$this->environment.'/'.$name.'.php')) {
			$config = include($this->folder.$this->environment.'/'.$name.'.php');
		} else if ($this->environment && file_exists($this->folder.$this->environment.'/'.$name.'.ini')) {
			$config = parse_ini_file($this->folder.$this->environment.'/'.$name.'.ini', true);
		} else if (file_exists($this->folder.$name.'.php')) {
			$config = include($this->folder.$name.'.php');
		} else if (file_exists($this->folder.$name.'.ini')) {
			$config = parse_ini_file($this->folder.$name.'.ini', true);
		} else {
			$config = null;
		}

		$this->set($name, $config);

		return $config;
	}



	/**
	 * Gets the configuration data. Loads automatically the data if it has not been loaded.
	 * If no name is defined, returns all loaded data
	 *
	 * @param $name The name of the configuration
	 * 
	 * @return mixed The configuration data or null
	 */
	public function get ($name = null) {
		if (func_num_args() === 0) {
			return $this->items;
		}

		if (!isset($this->items[$name])) {
			$this->load($name);
		}

		return $this->items[$name];
	}



	/**
	 * Stores a value in a configuration
	 * 
	 * $config->set('database', array(
	 *     'host' => 'localhost',
	 *     'database' => 'my-database',
	 *     'user' => 'admin',
	 *     'password' => '1234',
	 * ));
	 * 
	 * You can use an array directly to store more than one configuration:
	 * 
	 * $config->set(array(
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
	 * @param string $name The configuration name or an array with all configurations name and value
	 * @param mixed $value The value of the configuration
	 */
	public function set ($name, $value = null) {
		if (is_array($name)) {
			$this->items = array_replace($this->items, $name);
		} else {
			$this->items[$name] = $value;
		}
	}



	/**
	 * Deletes a configuration value
	 * 
	 * $config->delete('database');
	 * 
	 * or if you want delete just one sub-value
	 * 
	 * $config->delete('database', 'user');
	 *
	 * @param string $name The name of the configuration
	 * @param string $key The optional key of the configuration
	 */
	public function delete ($name, $key = null) {
		if (isset($key)) {
			unset($this->items[$name][$key]);

			if (!$this->items[$name]) {
				unset($this->items[$name]);
			}
		} else {
			unset($this->items[$name]);
		}
	}



	/**
	 * Checks if a configuration data exists
	 * 
	 * $config->check('database');
	 * 
	 * or if you want to check just one sub-value
	 * 
	 * $config->check('database', 'user');
	 * 
	 * @param string $name The name of the configuration
	 * @param string $key The optional key of the configuration
	 * 
	 * @return boolean True if the data exists, false if not.
	 */
	public function exists ($name, $key = null) {
		if (!array_key_exists($name, $this->items)) {
			return false;
		}

		return $key ? array_key_exists($key, $this->items[$name]) : true;
	}
}
?>