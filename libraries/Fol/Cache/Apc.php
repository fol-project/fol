<?php
namespace Fol;

class Cache_Apc implements Cache_Interface {

	/**
	 * public function __construct ()
	 *
	 * return none
	 */
	public function __construct () {
		if (!extension_loaded('apc')) {
			throw new \ErrorException('APC is not loaded');
		}
	}



	/**
	 * public function set ($name, [$value], [int $expire])
	 *
	 * Saves a value
	 * Returns boolean
	 */
	public function set ($name, $value, $expire = 3600) {
		return apc_store($name, $value, $expire);
	}



	/**
	 * public function get ($name)
	 *
	 * Returns a saved value
	 * Returns mixed
	 */
	public function get ($name) {
		return apc_fetch($name);
	}



	/**
	 * public function exists ($name)
	 *
	 * Returns if exists a value
	 * Returns boolean
	 */
	public function exists ($name) {
		return apc_exists($name);
	}



	/**
	 * public function delete ($name)
	 *
	 * Deletes a cached value
	 * Returns boolean
	 */
	public function delete ($name) {
		return apc_delete($name);
	}
}
?>