<?php
namespace Fol;

class Config {
	public $item = array();
	private $paths = array();



	/**
	 * public function load (string $name, [string $context])
	 *
	 * Returns mixed
	 */
	public function load ($name, $context = null) {
		$file = $name.'.php';
		$basedir = $this->path($context);

		$config = array();

		if (file_exists($basedir.ENVIRONMENT.'/'.$file)) {
			include ($basedir.ENVIRONMENT.'/'.$file);
		} else if (file_exists($basedir.$file)) {
			include ($basedir.$file);
		}

		$this->set($name, $config, $context);

		return $config;
	}



	/**
	 * public function get (string $name, [string $context])
	 *
	 * Returns mixed
	 */
	public function get ($name, $context = null) {
		if (is_null($context)) {
			if (isset($this->item[$name])) {
				return $this->item[$name];
			}

			return $this->load($name);
		}

		if (isset($this->item[$context][$name])) {
			return $this->item[$context][$name];
		}

		return $this->load($name, $context);
	}



	/**
	 * public function set (string $name, mixed $value, [string $context])
	 *
	 * Returns none
	 */
	public function set ($name, $value, $context = null) {
		if (is_null($context)) {
			$this->item[$name] = $value;
		} else {
			$this->item[$context][$name] = $value;
		}
	}



	/**
	 * private function path (string $context)
	 *
	 * Returns string
	 */
	private function path ($context) {
		return SCENE_PATH.'config/';
	}



	/**
	 * private function name (string $context, string $name)
	 *
	 * Returns array
	 */
	private function name ($context, $name) {
		if ($context === 'scene' && !$name) {
			global $Router;
			return $Router->scene;
		}

		return $name;
	}
}
?>