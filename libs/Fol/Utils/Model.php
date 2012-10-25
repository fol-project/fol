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

trait Model {
	private $_cache;

	protected static $Db;
	protected static $table;
	protected static $fields;

	/**
	 * static function to configure the model.
	 * Define the database, the table name and the available fields
	 * 
	 * @param PDO $Db The database object
	 * @param string $table The table name used in this model
	 * @param array $fields The name of all fields of the table
	 */
	public static function setDb (\PDO $Db, $table, array $fields) {
		static::$Db = $Db;
		static::$table = $table;
		static::$fields = $fields;
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
	public static function select ($query = null, array $marks = null) {
		$table = static::$table;

		$Query = static::$Db->prepare("SELECT * FROM `$table` $query");
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
		return current(static::select("$query LIMIT 1", $marks));
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
		$key = $name.implode($arguments);

		if (isset($this->_cache[$key])) {
			return $this->_cache[$key];
		}

		$method = '_'.$name;

		if (method_exists($this, $method)) {
			return $this->_cache[$key] = call_user_func_array(array($this, $method), $arguments);
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
		foreach ($data as $field => $value) {
			if (!in_array($field, static::$fields)) {
				throw new \Exception("The field '$field' does not exists");
			}

			$this->$field = $value;
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

		foreach (static::$fields as $field) {
			if (isset($this->$field)) {
				$data[$field] = $this->$field;
			}
		}

		unset($data['id']);

		//Insert
		if (empty($this->id)) {
			$table = static::$table;
			$fields = implode(', ', array_keys($data));
			$marks = implode(', ', array_fill(0, count($data), '?'));

			$Query = static::$Db->prepare("INSERT INTO `$table` ($fields) VALUES ($marks)");
			$result = $Query->execute(array_values($data));

			if ($result === true) {
				$this->id = static::$Db->lastInsertId();
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
			$result = $Query->execute(array_values($data));
		}

		return $result;
	}
}
?>