<?php
namespace Fol;

use Fol\Containers\Container;

class Config extends Container {
	private $basedir;



	/**
	 * public function __construct ([string $basedir])
	 *
	 * Returns none
	 */
	public function __construct ($basedir = null) {
		$this->setBaseDir($basedir ? $basedir : BASE_PATH.BASE_DIR);
	}



	/**
	 * public function setBaseDir (string $basedir)
	 *
	 * Returns none
	 */
	public function setBaseDir ($basedir) {
		$this->basedir = $basedir;
	}



	/**
	 * public function getBaseDir ()
	 *
	 * Returns string
	 */
	public function getBaseDir () {
		return $this->basedir;
	}



	/**
	 * public function setEnvironment (string $environment)
	 *
	 * Sets the environment subdirectory
	 * Returns none
	 */
	public function setEnvironment ($environment) {
		$this->environment = $environment;
	}



	/**
	 * public function getEnvironment ()
	 *
	 * Gets the environment subdirectory
	 * Returns string
	 */
	public function getEnvironment () {
		return $this->environment;
	}



	/**
	 * public function load (string $name)
	 *
	 * Loads the data of config files (in php or ini format)
	 * Returns mixed
	 */
	public function load ($name) {
		if (!$this->basedir) {
			return;
		}

		$file = $name.'.php';

		if ($this->environment && file_exists($this->basedir.$this->environment.'/'.$name.'.php')) {
			$config = include($this->basedir.$this->environment.'/'.$name.'.php');
		} else if ($this->environment && file_exists($this->basedir.$this->environment.'/'.$name.'.ini')) {
			$config = parse_ini_file($this->basedir.$this->environment.'/'.$name.'.ini', true);
		} else if (file_exists($this->basedir.$name.'.php')) {
			$config = include($this->basedir.$name.'.php');
		} else if (file_exists($this->basedir.$name.'.ini')) {
			$config = parse_ini_file($this->basedir.$name.'.ini', true);
		}

		$this->set($name, $config);

		return $config;
	}



	/**
	 * public function get ([string $name], [string $key])
	 *
	 * Returns mixed
	 */
	public function get ($name = null, $key = null) {
		if (func_num_args() === 0) {
			return $this->items;
		}

		if (!isset($this->items[$name])) {
			$this->load($name);
		}

		return $key ? $this->items[$name][$key] : $this->items[$name];
	}



	/**
	 * public function remove (string $name, [string $key])
	 *
	 * Returns none
	 */
	public function remove ($name, $key = null) {
		if ($key) {
			unset($this->items[$name][$key]);
		} else {
			unset($this->items[$name]);
		}
	}
}
?>