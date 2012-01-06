<?php
namespace Fol;

class Cache_File {

	/**
	 * public function set ($name, [$value], [int $expire])
	 *
	 * Saves a variable in a file
	 * Returns boolean
	 */
	public function set ($name, $value, $expire = 3600) {
		$filename = SCENE_PATH.'cache/'.md5($name);

		if (!is_file($filename) || is_writable($filename)) {
			file_put_contents($filename, serialize($value));

			touch($filename, time() + $expire);

			return true;
		}

		return false;
	}



	/**
	 * public function get ($name)
	 *
	 * Returns a variable saved in a file
	 * Returns mixed
	 */
	public function get ($name) {
		$filename = SCENE_PATH.'cache/'.md5($name);

		if (!is_file($filename) || (filemtime($filename) < time())) {
			return null;
		}

		return unserialize(file_get_contents($filename));
	}



	/**
	 * public function exists ($name)
	 *
	 * Returns if exists a file
	 * Returns boolean
	 */
	public function exists ($name) {
		$filename = SCENE_PATH.'cache/'.md5($name);

		if (!is_file($filename) || (filemtime($filename) < time())) {
			return null;
		}

		return true;
	}



	/**
	 * public function delete ($name)
	 *
	 * Deletes a cache file
	 * Returns boolean
	 */
	public function delete ($name) {
		$filename = SCENE_PATH.'cache/'.$name;

		return is_file($filename) ? unlink($filename) : null;
	}
}
?>