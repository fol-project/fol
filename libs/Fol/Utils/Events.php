<?php
/**
 * Fol\Utils\Events
 * 
 * Provides a basic Events management
 * Example:
 * 
 * class User {
 * 	use Fol\Utils\Events;
 * 
 *  public function login () {
 *   //login code
 *   $this->trigger('login');
 *  }
 * }
 * 
 * $User = new User();
 * 
 * $User->on('login', function () {
 * 	echo 'User login';
 * });
 * 
 * $User->login(); //echo "User login"
 */
namespace Fol\Utils;

trait Events {
	private $events = array();

	/**
	 * Define a new event
	 * 
	 * @param string $name Event name
	 * @param callable $callback The event callback
	 */
	public function on ($name, callable $callback) {
		$this->events[$name] = $callback;
	}


	/**
	 * Remove an event listener
	 * 
	 * @param string $name Event name
	 */
	public function off ($name) {
		unset($this->events[$name]);
	}


	/**
	 * Execute an event listener
	 * 
	 * @param string $name Event name
	 * @param mixed $arg1 Argument for the callback
	 * ...
	 * 
	 * @return mixed The returns of the callback or null
	 */
	public function trigger ($name) {
		if (isset($this->events[$name])) {
			return call_user_func_array($this->events[$name], func_get_args());
		}

		return null;
	}
}
?>