<?php
namespace Fol;

class Cache_File implements Cache_Interface {
	private $folder;


	/**
	 * public function __construct ([string $folder])
	 *
	 * Returns object/none
	 */
	public function __construct ($folder = null) {
		if ($folder) {
			$this->setFolder($folder);
		}
	}



	/**
	 * public function setFolder (string $folder)
	 *
	 * Sets the cache folder
	 * Returns none
	 */
	public function setFolder ($folder) {
		$this->folder = $folder;
	}



	/**
	 * public function getFolder ()
	 *
	 * Returns string
	 */
	public function getFolder ($name = null) {
		return $this->folder;
	}




	/**
	 * public function set ($name, [$value], [int $expire])
	 *
	 * Saves a value in a file
	 * Returns boolean
	 */
	public function set ($name, $value, $expire = 3600) {
		$filename = $this->filename($name);

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
	 * Returns a value saved in a file
	 * Returns mixed
	 */
	public function get ($name) {
		if (!$this->exists($name)) {
			return false;
		}

		return unserialize(file_get_contents($this->filename($name)));
	}



	/**
	 * public function exists ($name)
	 *
	 * Returns if a value exists
	 * Returns boolean
	 */
	public function exists ($name) {
		$filename = $this->filename($name);

		if (!is_file($filename)) {
			return false;
		}

		if (filemtime($filename) < time()) {
			unlink($filename);
			return false;
		}

		return true;
	}



	/**
	 * public function delete ($name)
	 *
	 * Deletes a value
	 * Returns boolean
	 */
	public function delete ($name) {
		$filename = $this->filename($name);

		return is_file($filename) ? unlink($filename) : true;
	}



	/**
	 * private function filename ($name)
	 *
	 * Gets the real name of a cached file
	 * Returns string
	 */
	private function filename ($name) {
		return $this->folder.md5($name);
	}
}
?>