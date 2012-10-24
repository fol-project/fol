<?php
/**
 * Fol\Utils\Service
 * 
 * Provides a simple dependecy injection manager for any class
 * Example:
 * 
 * class App {
 * 	use Fol\Utils\Service
 * }
 * 
 * $App = new App;
 * 
 * $App->register('database', function () {
 * 	$dsn = 'mysql:dbname=testdb;host=127.0.0.1';
 *  $user = 'dbuser';
 *  $password = 'dbpass';
 * 
 *  try {
 *     $dbh = new PDO($dsn, $user, $password);
 *  } catch (PDOException $e) { 
 *     echo 'Connection failed: ' . $e->getMessage();
 *  }
 * 	return $dbh;
 * });
 * 
 * $result = $App->database->query('SELECT * FROM items');
 */
namespace Fol\Utils;

trait Services {
	private $services;


	/**
	 * Register a new service
	 * 
	 * @param string $name The service name
	 * @param Closure $resolver A function that returns a service instance
	 */
	public function register ($name, \Closure $resolver = null) {
		if (is_array($name)) {
			foreach ($name as $name => $resolver) {
				$this->register($name, $resolver);
			}

			return;
		}

		$this->services[$name] = $resolver;
	}



	/**
	 * Deletes a service
	 * 
	 * @param string $name The service name
	 */
	public function unregister ($name) {
		unset($this->services[$name]);
	}



	/**
	 * Check if a service is registered
	 * 
	 * @param string $name The servize name
	 */
	public function registered ($name) {
		return isset($this->services[$name]);
	}



	/**
	 * Gets an instance of a service
	 * 
	 * @param string $name The service name
	 * 
	 * @return object The class instance or false if the service is not defined
	 */
	public function get ($name) {
		if ($this->registered($name) === false) {
			throw new \Exception("There is not any service registered with the name '$name'");
		}

		return $this->services[$name]();
	}


	/**
	 * Magic function to generate and save the services
	 * 
	 * @param string $name The service name
	 */
	public function __get ($name) {
		if ($this->registered($name)) {
			return $this->$name = $this->get($name);
		}

		return parent::__get($name);
	}
}
?>