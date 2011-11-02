<?php
namespace Fol;

class Config {
	public $config = array();
	private $paths = array();



	/**
	 * public function load (string $file, [string $context], [string $name])
	 *
	 * Returns mixed
	 */
	public function load ($file, $context = 'scene', $name = null) {
		$filename = $file.'.php';
		$basedir = $this->path($context, $name);

		$config = array();

		if (file_exists($basedir.ENVIRONMENT.'/'.$filename)) {
			include ($basedir.ENVIRONMENT.'/'.$filename);
		} else if (file_exists($basedir.$filename)) {
			include ($basedir.$filename);
		}

		$this->set($file, $config, $context, $name);

		return $config;
	}



	/**
	 * public function get (string $file, [string $context], [string $name])
	 *
	 * Returns mixed
	 */
	public function get ($file, $context = 'scene', $name = null) {
		$store = $context.'/'.$name.'/'.$file;

		if (isset($this->config[$store])) {
			return $this->config[$store];
		}

		return $this->load($file, $context, $name);
	}



	/**
	 * public function set (string $file, mixed $value, [string $context], [string $name])
	 *
	 * Returns none
	 */
	public function set ($file, $value, $context = 'scene', $name = null) {
		$this->config[$context.'/'.$name.'/'.$file] = $value;
	}



	/**
	 * private function path (string $context, string $name)
	 *
	 * Returns string
	 */
	private function path ($context, $name) {
		$path_name = $context.'/'.$name;

		if ($this->paths[$path_name]) {
			return $this->paths[$path_name];
		}

		if ($context === 'base') {
			return $this->paths[$path_name] = BASE_PATH.'Fol/config/';
		}

		if ($context === 'scene') {
			$scenes = $this->get('scenes', 'base');

			if ($name) {
				return $this->paths[$path_name] = $scenes[$name]['path'].'config/';
			}

			global $Router;

			return $this->paths[$path_name] = $scenes[$Router->scene]['path'].'config/';
		}
	}
}
?>