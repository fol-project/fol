<?php
/**
 * Fol\Session
 * 
 * Class to manage the session
 */
namespace Fol;

class Session {
	protected $cookies;
	protected $cookieParams;

	/**
	 * Constructor. Start/resume the latest session.
	 * 
	 * @throws an Exception is the session is disabled
	 */
	public function __construct (array $cookies, $id = null, $name = null) {
		switch (session_status()) {
			case PHP_SESSION_DISABLED:
				throw new \Exception('Session are disabled');
				break;

			case PHP_SESSION_NONE:
				ini_set('session.use_only_cookies', 1);

				$this->cookieParams = session_get_cookie_params();

				$this->setCookieParams([
					'httponly' => true,
					'path' => BASE_URL ?: '/'
				]);

				$this->start($id, $name);
				break;
		}

		$this->cookies = $cookies;
	}


	/**
	 * Sets the session cookie parameters
	 * @param array $params The available parameters (lifetime, path, domain, secure, httponly)
	 */
	public function setCookieParams (array $params) {
		$this->cookieParams = array_replace($this->cookieParams, $params);

		session_set_cookie_params($this->cookieParams['lifetime'], $this->cookieParams['path'], $this->cookieParams['domain'], $this->cookieParams['secure'], $this->cookieParams['httponly']);
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
		$this->start(sha1(mt_rand()), $name);
	}


	/**
	 * Close the session and save the data.
	 */
	public function close () {
		if ($this->isStarted()) {
			session_write_close();
		}
	}


	/**
	 * Sets the session cache expire in minutes
	 * 
	 * @param int $minutes The time in minutes
	 */
	public function setCacheExpire ($minutes) {
		return session_cache_expire($minutes);
	}


	/**
	 * Gets the session cache expire in minutes
	 *
	 * @return int
	 */
	public function getCacheExpire () {
		return session_cache_expire();
	}


	/**
	 * Start a session
	 * 
	 * @param string $id Set the custom id if the session has not a previous id assigned. Useful to switch from one session to another.
	 * @param string $name The session name.
	 */
	public function start ($id = null, $name = null) {
		if ($name !== null) {
			$this->setName($name);
		}

		if ($id !== null) {
			$this->setId(isset($this->cookies[$name]) ? $this->cookies[$name] : $id);
		}

		session_start();
	}


	/**
	 * Destroy the current session deleting the data
	 */
	public function destroy () {
		if (!$this->isStarted()) {
			$this->start();
		}

		$this->remove();

		return session_destroy();
	}


	/**
	 * Check if a session is started or not.
	 * 
	 * @return boolean True if it's started, false if not
	 */
	public function isStarted () {
		return (session_status() === PHP_SESSION_ACTIVE);
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
	 * Sets the current session name
	 * 
	 * @return string The session name
	 */
	public function setName ($name) {
		return session_name($name);
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
		return session_regenerate_id();
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
		if ($name === null) {
			return $_SESSION;
		}

		if (isset($_SESSION[$name])) {
			return $_SESSION[$name];
		}

		return $default;
	}


	/**
	 * Set a new or update an existing variable
	 * 
	 * @param string/array $name The variable name or an array of variables
	 * @param string $value The value of the variable
	 */
	public function set ($name, $value = null) {
		if (is_array($name)) {
			$_SESSION = array_replace($_SESSION, $name);
		} else {
			$_SESSION[$name] = $value;
		}
	}


	/**
	 * Delete one or all variables of the session
	 * 
	 * @param string $name The variable name. If it is not defined, delete all variables
	 */	
	public function delete ($name = null) {
		if ($name === null) {
			$_SESSION = [];
			session_unset();
		} else {
			unset($_SESSION[$name]);
		}
	}


	/**
	 * Check if a variable is defined or not
	 * 
	 * @param string $name The variable name.
	 * 
	 * @return boolean True if it's defined, false if not
	 */
	public function has ($name) {
		return array_key_exists($name, $_SESSION);
	}


	/**
	 * Get a flash value (read only once)
	 * 
	 * @param string $name The value name. If it is not defined, returns all stored variables
	 * @param string $default A default value in case the variable is not defined
	 * 
	 * @return string The value of the variable or the default value.
	 * @return array All stored variables in case no name is defined.
	 */
	public function getFlash ($name = null, $default = null) {
		if ($name === null) {
			return isset($_SESSION['_flash']) ? $_SESSION['_flash'] : [];
		}

		if (isset($_SESSION['_flash'][$name])) {
			$default = $_SESSION['_flash'][$name];
			unset($_SESSION['_flash'][$name]);
		}

		return $default;
	}


	/**
	 * Set a new flash value
	 * 
	 * @param string/array $name The variable name or an array of variables
	 * @param string $value The value of the variable
	 */
	public function setFlash ($name, $value = null) {
		if (!isset($_SESSION['_flash'])) {
			$_SESSION['_flash'] = [];
		}

		if (is_array($name)) {
			$_SESSION['_flash'] = array_replace($_SESSION['_flash'], $name);
		} else {
			$_SESSION['_flash'][$name] = $value;
		}
	}


	/**
	 * Check if a flash variable is defined or not (but does not remove it)
	 * 
	 * @param string $name The variable name.
	 * 
	 * @return boolean True if it's defined, false if not
	 */
	public function hasFlash ($name) {
		return (isset($_SESSION['_flash']) && array_key_exists($name, $_SESSION['_flash']));
	}
}
