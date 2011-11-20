<?php
namespace Fol\Cache;

class Apc {

	/**
	 * public function __construct ()
	 *
	 * return none
	 */
	public function __construct () {
		if (!extension_loaded('apc')) {
			die('apc is not loaded');
		}
	}



	/**
	 * public function set ($name, [$value], [int $expire])
	 *
	 * Saves a variable in apc cache
	 * Returns boolean
	 */
	public function set ($name, $value, $expire = 3600) {
		return apc_store($name, $value, $expire);
	}



	/**
	 * public function get ($name)
	 *
	 * Returns a variable saved in apc
	 * Returns mixed
	 */
	public function get ($name) {
		return apc_fetch($name);
	}



	/**
	 * public function exists ($name, [$expire])
	 *
	 * Returns if exists a file
	 * Returns boolean
	 */
	public function exists ($name, $expire = null) {
		return apc_exists($name);
	}



	/**
	 * public function delete ($name)
	 *
	 * Deletes a cache file
	 * Returns boolean
	 */
	public function delete ($name) {
		return apc_delete($name);
	}
}
?>