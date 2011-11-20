<?php
namespace Fol\Cache;

class File {

	/**
	 * public function set ($name, [$value])
	 *
	 * Saves a variable in a file
	 * Returns boolean
	 */
	public function set ($name, $value) {
		$filename = SCENE_PATH.'cache/'.$name;

		if (!is_file($filename) || is_writable($filename)) {
			file_put_contents($filename, serialize($value));

			return true;
		}

		return false;
	}



	/**
	 * public function get ($name, [$expire])
	 *
	 * Returns a variable saved in a file
	 * Returns mixed
	 */
	public function get ($name, $expire = null) {
		$filename = SCENE_PATH.'cache/'.$name;

		if (!is_file($filename) || (!is_null($expire) && (filemtime($filename) + $expire) < time())) {
			return null;
		}

		return unserialize(file_get_contents($filename));
	}



	/**
	 * public function exists ($name, [$expire])
	 *
	 * Returns if exists a file
	 * Returns boolean
	 */
	public function exists ($name, $expire = null) {
		$filename = SCENE_PATH.'cache/'.$name;

		if (!is_file($filename) || (!is_null($expire) && (filemtime($filename) + $expire) < time())) {
			return false;
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