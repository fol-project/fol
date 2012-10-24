<?php
/**
 * Fol\Data
 * 
 * This is a simple class to load and save data from php files (that returns an array)
 * You must define a base folder and the class search for the files inside automatically. It's useful for configuration management or any other data without use databases
 * 
 * Example:
 * $data->get('database');
 * 
 * data_folder/database.php
 * 
 * You can define an environment (for example localhost) to load different data in different environments:
 * 
 * $data->environment = 'localhost';
 * $data->get('database');
 * 
 * data_folder/localhost/database.php
 * data_folder/database.php
 * 
 * The class will search the database data in this two files. If the first file is found, stops and don't search in the second.
 */
namespace Fol;

class Data {
	public $environment;

	private $folder;
	private $items = array();


	/**
	 * Constructor. You can define the folder where search the data
	 *
	 * $data = new Fol\Data('apps/my_app/data')
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

		throw new ErrorException('The folder "'.$folder.'" does not exists');
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
	 * Loads the data from php file (that returns the value)
	 * 
	 * @param string $name The name of the data (must be the name of the files where the data are stored)
	 * @param boolean $merge True to merge the loaded data, false to replace
	 * 
	 * @return mixed The data or null if doesn't exists
	 */
	public function load ($name, $merge = true) {
		if (!$this->folder) {
			throw new ErrorException('The base folder is not defined');

			return false;
		}

		if ($this->environment && is_file($this->folder.$this->environment.'/'.$name.'.php')) {
			$data = include($this->folder.$this->environment.'/'.$name.'.php');
		} else if (is_file($this->folder.$name.'.php')) {
			$data = include($this->folder.$name.'.php');
		} else {
			$data = null;
		}

		if ($merge === true) {
			$this->merge($name, $data);
		} else {
			$this->set($name, $data);
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
			$this->load($name);
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
	 * @param mixed $value The value of the data
	 */
	public function set ($name, $value = null) {
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
	 * @param mixed $value The value of the data
	 */
	public function merge ($name, $value = null) {
		if (!isset($this->items[$name])) {
			$this->items[$name] = $value;
		} else if (is_array($name)) {
			$this->items = array_replace_recursive($this->items, $name);
		} else if ($value) {
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
	 * $data->check('database');
	 * 
	 * or if you want to check just one sub-value
	 * 
	 * $data->check('database', 'user');
	 * 
	 * @param string $name The name of the data
	 * @param string $key The optional key of the data
	 * 
	 * @return boolean True if the data exists, false if not.
	 */
	public function exists ($name, $key = null) {
		if (!array_key_exists($name, $this->items)) {
			return false;
		}

		return $key ? array_key_exists($key, $this->items[$name]) : true;
	}


	/**
	 * Saves the data in the file.
	 * If no name is defined, returns all loaded data
	 *
	 * @param string $name The name of the data
	 * @param string $environment The environment where the data will be saved. If it's not defined, uses the current environment
	 */
	public function saveFile ($name, $environment = null) {
		$data = $this->get($name);
		$environment = ($environment === null) ? $this->environment : $environment;
		$path = $this->folder.($environment ? '/'.$environment : ($this->environment ? '/'.$this->environment : ''));

		if (!is_dir($path)) {
			if (mkdir($path, 0777, true) === false) {
				return false;
			}
		}

		if (file_put_contents($path.'/'.$name.'.php', '<?php return '.var_export($data, true).'; ?>') === false) {
			return false;
		}

		return true;
	}


	/**
	 * Removes the data in the file.
	 * If no name is defined, returns all loaded data
	 *
	 * @param string $name The name of the data
	 * @param string $environment The environment where the data will be saved. If it's not defined, uses the current environment
	 */
	public function deleteFile ($name = null, $environment = null) {
		$data = $this->get($name);
		$environment = ($environment === null) ? $this->environment : $environment;
		$path = $this->folder.($environment ? '/'.$environment : ($this->environment ? '/'.$this->environment : ''));

		if (is_file($path.'/'.$name.'.php') && unlink($path.'/'.$name.'.php') === false) {
			return false;
		}

		return true;
	}
}
?>
