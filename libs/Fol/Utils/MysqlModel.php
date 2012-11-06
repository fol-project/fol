<?php
/**
 * Fol\Utils\Model
 * 
 * Provides a simple model with basic database operations.
 * Example:
 * 
 * class Items {
 * 	use Fol\Utils\Model;
 * 	
 * 	public static function setDb ($Db) {
 * 		parent::setDb($Db, 'items', array('id', 'name', 'description'));
 * 	}
 * }
 * 
 * $Item = Items::create(array(
 * 	'name' => 'Item name',
 * 	'description' => 'Item description'
 * ));
 * 
 * $Item->save();
 * $Item->name = 'New name for the item';
 * $Item->save();
 */
namespace Fol\Utils;

trait MysqlModel {
	private $_cache = array();
	private $_error;

	protected static $Db;
	protected static $table;
	protected static $fields;


	/**
	 * static function to configure the model.
	 * Define the database, the table name and the available fields
	 * 
	 * @param PDO $Db The database object
	 * @param string $table The table name used in this model
	 * @param array $fields The name of all fields of the table. If it's not defined, execute a DESCRIBE query
	 */
	public static function setDb (\PDO $Db, $table, array $fields = null) {
		static::$Db = $Db;
		static::$table = $table;
		static::$fields = $fields;
	}


	/**
	 * static function to return all available fields of this model
	 * 
	 * @return array The database fields names of this model
	 */
	public static function getFields () {
		if (static::$fields === null) {
			$table = static::$table;
			return static::$fields = static::$Db->query("DESCRIBE `$table`", \PDO::FETCH_COLUMN, 0)->fetchAll();
		}

		return static::$fields;
	}


	/**
	 * returns the model queries ready to use in a mysql query
	 * This function is useful to "import" a model inside another, you just have to include the fields names of the model.
	 * 
	 * Example:
	 * $fieldsQuery = User::getQueryFields();
	 * $posts = Post::select("posts.*, $fieldsQuery FROM posts, users WHERE posts.author = users.id");
	 * $posts[0]->User //The user model inside the post
	 * 
	 * @param string $name The name of the parameter used to the sub-model. If it's not defined, uses the model class name (without the namespace)
	 * 
	 * @return string The portion of mysql code with the fields names
	 */
	public static function getQueryFields ($name = null) {
		$table = static::$table;
		$fields = array();
		$class = get_called_class();

		if ($name === null) {
			$name = (($pos = strrpos($class, '\\')) === false) ? $class : substr($class, $pos + 1);
		}

		foreach (static::getFields() as $field) {
			$fields[] = "`$table`.`$field` as `$class::$name::$field`";
		}

		return implode(', ', $fields);
	}


	/**
	 * Constructor class that executes automatically the resolveFields method
	 */
	public function __construct () {
		$this->resolveFields();
	}


	/**
	 * Resolve the fields included using the getQueryFields method
	 */
	public function resolveFields () {
		$extracted = array();

		foreach ($this as $key => $value) {
			if (strpos($key, '::') !== false) {
				list($class, $name, $field) = explode('::', $key, 3);

				if (!isset($this->$name)) {
					$this->$name = new $class();
				}

				$this->$name->$field = $value;
				unset($this->$key);
			}
		}
	}


	/**
	 * Execute a selection and returns an array with the models result
	 * 
	 * Examples:
	 * $AllItems = Item::select();
	 * $LatestItems = Items::select('SORT BY date DESC LIMIT 5');
	 * $BlueItems = Items::select('WHERE color = :color', array(':color' => 'blue'));
	 * 
	 * @param string $query The query for the selection
	 * @param array $marks Optional marks used in the query
	 * 
	 * @return array The result of the query or false if there was an error
	 */
	public static function select ($query = '', array $marks = null) {
		if (stripos($query, ' FROM ') === false) {
			$table = static::$table;
			$query = "* FROM `$table` $query";
		}

		$Query = static::$Db->prepare("SELECT $query");
		$Query->execute($marks);

		return $Query->fetchAll(\PDO::FETCH_CLASS, get_called_class());
	}


	/**
	 * Execute a selection of just one element.
	 * 
	 * Example:
	 * $Item = Item::selectOne('WHERE title = :title', array(':title' => 'My item title'))
	 * 
	 * @param string $query The query for the selection. Note that "LIMIT 1" will be automatically appended
	 * @param array $marks Optional marks used in the query
	 * 
	 * @return object The result of the query or false if there was an error
	 */
	public static function selectOne ($query = null, array $marks = null) {
		if (stripos($query, 'LIMIT ') === false) {
			$query .= ' LIMIT 1';
		}

		return current(static::select($query, $marks));
	}


	/**
	 * Shortcut to select a row by id
	 * 
	 * Example:
	 * $Item = Item::selectById(45);
	 * 
	 * @param int $id The row id.
	 * 
	 * @return object The result of the query or false if there was an error
	 */
	public static function selectById ($id) {
		return static::selectOne('WHERE id = :id', array(':id' => $id));
	}


	/**
	 * Magic method to make selections and save the result in the cache.
	 * You must define a method started by "_".
	 * 
	 * Example:
	 * MyMethod {
	 * 	use Fol\Utils\Model;
	 * 
	 * 	_getColors () {
	 * 		return static::DB->query('SELECT * FROM colors');
	 * 	}
	 * 
	 *  _getColor ($color) {
	 * 		return static::DB->query("SELECT * FROM colors WHERE color = '$color'");
	 * 	}
	 * }
	 * 
	 * $Item = new MyMethod;
	 * $Item->getColors(); //Execute the selection
	 * $Item->getColors(); //Don't execute the selection again, returns the cached result
	 * $Item->getColor('blue'); //Execute the selection
	 * $Item->getColor('red'); //Execute the selection (the argument is different)
	 * $Item->getColor('blue'); //Don't execute the selection again, returns the cached result
	 */
	public function __call ($name, $arguments) {
		$key = array();

		foreach ($arguments as $argument) {
			$key[] = is_object($argument) ? spl_object_hash($argument) : $argument;
		}

		$key = $name.implode($key);

		if (array_key_exists($key, $this->_cache)) {
			return $this->_cache[$key];
		}

		$method = '_'.$name;

		if (method_exists($this, $method)) {
			return $this->_cache[$key] = call_user_func_array(array($this, $method), $arguments);
		}

		throw new \Exception("The function $name is not defined");
	}

	
	/**
	 * Creates a empty object or, optionally, fill with some data
	 * 
	 * @param array $data Data to fill the option.
	 * 
	 * @return object The instantiated objec
	 */
	public static function create (array $data = null) {
		$Item = new static();

		if ($data !== null) {
			$Item->edit($data);
		}

		return $Item;
	}


	/**
	 * Edit the data of the object using an array (but doesn't save it into the database)
	 * It's the same than edit the properties of the object but check if the property name is in the fields list
	 * 
	 * @param array $data The new data
	 */
	public function edit (array $data) {
		$fields = static::getFields();

		foreach ($data as $field => $value) {
			if (!in_array($field, $fields)) {
				throw new \Exception("The field '$field' does not exists");
			}

			$this->$field = $value;
		}
	}


	/**
	 * Deletes the properties of the model (but not in the database)
	 */
	public function clean () {
		foreach (static::getFields() as $field) {
			unset($this->$field);
		}
	}


	/**
	 * Saves the object data into the database. If the object has the property "id", makes an UPDATE, otherwise makes an INSERT
	 * 
	 * @param array $data Optional new data to define before save
	 */
	public function save (array $data = null) {
		if ($data !== null) {
			$this->edit($data);
		}

		$data = array();

		foreach (static::getFields() as $field) {
			$data[$field] = isset($this->$field) ? $this->$field : null;
		}

		unset($data['id']);

		if (($data = $this->prepareToSave($data)) === false) {
			return false;
		}

		foreach ($data as $field => $value) {
			if ($value === null) {
				unset($data[$field]);
			}
		}

		//Insert
		if (empty($this->id)) {
			$table = static::$table;
			$fields = implode(', ', array_keys($data));
			$marks = implode(', ', array_fill(0, count($data), '?'));

			$Query = static::$Db->prepare("INSERT INTO `$table` ($fields) VALUES ($marks)");
			$result = $Query->execute(array_values($data));

			if ($result === true) {
				$this->id = static::$Db->lastInsertId();
			} else {
				$this->_error = static::$Db->errorInfo();
			}

		//Update
		} else {
			$table = static::$table;
			$set = array();
			$id = intval($this->id);

			foreach ($data as $field => $value) {
				$set[] = "`$field` = ?";
			}

			$set = implode(', ', $set);

			$Query = static::$Db->prepare("UPDATE `$table` SET $set WHERE id = $id LIMIT 1");

			if (($result = $Query->execute(array_values($data))) === false) {
				$this->_error = static::$Db->errorInfo();
			}
		}

		return $result;
	}


	/**
	 * Deletes the current row in the database (but keep the data in the object)
	 * 
	 * @return boolean True if the register is deleted, false if any error happened
	 */
	public function delete () {
		if (!empty($this->id)) {
			$table = static::$table;
			$id = intval($this->id);

			if (static::$Db->exec("DELETE FROM `$table` WHERE id = $id LIMIT 1") !== false) {
				$this->id = null;

				return true;
			}
		}

		return false;
	}


	/**
	 * Prepare the data before to save. Useful to validate or transform data before save in database
	 * This function is provided to be overwrited by the class that uses this trait
	 * 
	 * @param array $data The data to save.
	 * 
	 * @return array The transformed data. If returns false, the data will be not saved.
	 */
	public function prepareToSave (array $data) {
		return $data;
	}


	/**
	 * Return the last mysql error
	 * 
	 * @return array The error info or null
	 */
	public function getError () {
		return $this->_error;
	}
}
?>