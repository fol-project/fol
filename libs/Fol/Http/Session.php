<?php
/**
 * Fol\Http\Session
 * 
 * Class to manage the session
 */
namespace Fol\Http;

use Fol\Http\Container;

class Session extends Container {
	static private $Data;

	/**
	 * Constructor. Start/resume the latest session.
	 * 
	 * @throws an Exception is the session is disabled
	 */
	public function __construct () {
		switch (session_status()) {
			case PHP_SESSION_DISABLED:
				throw new \Exception('Session are disabled');
				break;

			case PHP_SESSION_NONE:
				ini_set('session.use_only_cookies', 1);

				$params = session_get_cookie_params();
				$params['httponly'] = true;
				$params['path'] = BASE_URL;

				session_set_cookie_params($params['lifetime'], $params['path'], $params['domain'], $params['secure'], $params['httponly']);

				$this->start();
				break;
		}

		if (!isset(self::$Data)) {
			self::$Data = new Container($_SESSION);
		}
	}



	/**
	 * Magic function to close the session on destroy the object
	 */
	public function __destruct () {
		$this->close();
	}


	/**
	 * Switch the current session to other different session
	 * 
	 * @param string $name The name of the new session
	 */
	public function switchTo ($name) {
		if (session_name() === $name) {
			return;
		}

		$this->close();
		$this->start($name, sha1(mt_rand()));
	}


	/**
	 * Close the session and save the data.
	 */
	public function close () {
		if ($this->isStarted()) {
			$_SESSION = self::$Data->get();

			session_write_close();
		}

		self::$Data = null;
	}


	/**
	 * Start a session
	 * 
	 * @param string $name The session name.
	 * @param string $initial_id Set the custom id if the session has not a previous id assigned. Useful to switch from one session to another.
	 */
	public function start ($name = null, $initial_id = null) {
		if ($name !== null) {
			session_name($name);
		}

		if ($initial_id !== null) {
			session_id(isset($_COOKIE[$name]) ? $_COOKIE[$name] : $initial_id);
		}

		session_start();

		self::$Data = new Container($_SESSION);
	}


	/**
	 * Destroy the current session deleting the data
	 */
	public function destroy () {
		$_SESSION = array();

		if (ini_get("session.use_cookies")) {
			$params = session_get_cookie_params();
			setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
		}

		session_destroy();

		self::$Data = null;
	}


	/**
	 * Check if a session is started or not.
	 * 
	 * @return boolean True if it's started, false if not
	 */
	public function isStarted () {
		return (session_status() === PHP_SESSION_ACTIVE) ? true : false;
	}


	/**
	 * Get the current session name
	 * 
	 * @return string The session name
	 */
	public function getName () {
		return session_name();
	}


	/**
	 * Get the current session id
	 * 
	 * @return string The id
	 */
	public function getId () {
		return session_id();
	}


	/**
	 * Set a new id for the current session
	 * 
	 * @param string $id The new id
	 * 
	 * @return string The previous session id
	 */
	public function setId ($id) {
		return session_id($id);
	}


	/**
	 * Regenerate the id for the current session
	 */
	public function regenerateId () {
		session_regenerate_id();
	}


	/**
	 * Get a value from the current session
	 * 
	 * @param string $name The value name. If it is not defined, returns all stored variables
	 * @param string $default A default value in case the variable is not defined
	 * 
	 * @return string The value of the variable or the default value.
	 * @return array All stored variables in case no name is defined.
	 */
	public function get ($name = null, $default = null) {
		return self::$Data->get($name, $default);
	}


	/**
	 * Set a new or update an existing variable
	 * 
	 * @param string/array $name The variable name or an array of variables
	 * @param string $value The value of the variable
	 */
	public function set ($name, $value = null) {
		self::$Data->set($name, $value);
	}


	/**
	 * Delete one or all variables of the session
	 * 
	 * @param string $name The variable name. If it is not defined, delete all variables
	 */	
	public function delete ($name = null) {
		self::$Data->delete($name);
	}


	/**
	 * Check if a variable is defined or not
	 * 
	 * @param string $name The variable name.
	 * 
	 * @return boolean True if it's defined, false if not
	 */
	public function exists ($name) {
		return self::$Data->exists($name);
	}
}
?>