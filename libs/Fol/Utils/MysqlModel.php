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
	public static function setDb (\PDO $Db, $table = null, array $fields = null) {
		static::$Db = $Db;
		static::$table = ($table === null) ? strtolower(substr(strrchr(get_called_class(), '\\'), 1)) : $table;
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


	static private function generateSelectQuery (array $query) {
		$string = 'SELECT '.(isset($query['SELECT']) ? implode(', ', (array)$query['SELECT']) : '*');
		$string .= ' FROM '.implode(', ', (array)$query['FROM']);

		if (isset($query['LEFT JOIN'])) {
			$string .= ' LEFT JOIN '.$query['LEFT JOIN'];
		}

		if (isset($query['INNER JOIN'])) {
			$string .= ' INNER JOIN '.$query['INNER JOIN'];
		}

		if (isset($query['WHERE'])) {
			$string .= ' WHERE '.implode(' AND ', (array)$query['WHERE']);
		}

		if (isset($query['GROUP BY'])) {
			$string .= ' GROUP BY '.implode(', ', (array)$query['GROUP BY']);
		}

		if (isset($query['ORDER BY'])) {
			$string .= ' ORDER BY '.implode(', ', (array)$query['ORDER BY']);
		}

		if (isset($query['LIMIT'])) {
			$string .= ' LIMIT '.implode(', ', (array)$query['LIMIT']);
		}

		return $string;
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
			$fields[] = "`$table`.`$field` as `$class::$field::$name`";
		}

		return implode(', ', $fields);
	}


	/**
	 * Constructor class that executes automatically the resolveFields method
	 * and ensure all parameteres are initialized
	 */
	public function __construct () {
		foreach (static::getFields() as $field) {
			if (!isset($this->$field)) {
				$this->$field = null;
			}
		}

		$this->resolveFields();
	}


	/**
	 * Resolve the fields included using the getQueryFields method
	 */
	public function resolveFields () {
		$fields = array();

		foreach ($this as $key => $value) {
			if (strpos($key, '::') !== false) {
				list($class, $field, $name) = explode('::', $key, 3);

				if (!isset($this->$name)) {
					$fields[] = $name;
					$this->$name = (new \ReflectionClass($class))->newInstanceWithoutConstructor();
				}

				$this->$name->$field = $value;
				unset($this->$key);
			}
		}

		foreach ($fields as $name) {
			$this->$name->__construct();
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
		if (is_array($query)) {
			if (!isset($query['SELECT'])) {
				$query['SELECT'] = array('*');
			}
			if (!isset($query['FROM'])) {
				$query['FROM'] = array(static::$table);
			}

			$query = self::generateSelectQuery($query);
		} else if (stripos($query, ' FROM ') === false) {
			$query = 'SELECT * FROM `'.static::$table.'` '.$query;
		} else {
			$query = "SELECT $query";
		}

		$Query = static::$Db->prepare($query);
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
		if (is_array($query)) {
			if (!isset($query['LIMIT'])) {
				$query['LIMIT'] = 1;
			}
		} else if (stripos($query, ' LIMIT ') === false) {
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
	 * Magic method to execute functions and save the result in the cache.
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
	 * Magic method to execute 'get' functions and save the result in a property.
	 * 
	 * Example:
	 * MyMethod {
	 * 	use Fol\Utils\Model;
	 * 
	 * 	getColors () {
	 * 		return static::DB->query('SELECT * FROM colors');
	 * 	}
	 * 
	 * $Item = new MyMethod;
	 * $Item->colors; //Execute the method "getColors" and save the result in the property colors
	 */
	public function __get ($name) {
		$method = "get$name";

		if (method_exists($this, $method) || method_exists($this, '_'.$method)) {
			return $this->$name = $this->$method();
		}
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
			$this->$field = null;
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

		if (($data = $this->prepareToSave($data)) === false) {
			return false;
		}

		unset($data['id']);

		foreach ($data as $field => $value) {
			if ($value === null) {
				unset($data[$field]);
			}
		}

		$table = static::$table;

		//Insert
		if (empty($this->id)) {
			$fields = implode(', ', array_keys($data));
			$marks = implode(', ', array_fill(0, count($data), '?'));

			$Query = static::$Db->prepare("INSERT INTO `$table` ($fields) VALUES ($marks)");

			if ($Query->execute(array_values($data)) === false) {
				var_dump($Query->errorInfo());
				return false;
			}
			
			$this->id = static::$Db->lastInsertId();

			return true;
		}

		//Update
		$set = array();
		$id = intval($this->id);

		foreach ($data as $field => $value) {
			$set[] = "`$field` = ?";
		}

		$set = implode(', ', $set);

		$Query = static::$Db->prepare("UPDATE `$table` SET $set WHERE id = $id LIMIT 1");

		return $Query->execute(array_values($data));
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
}
?>