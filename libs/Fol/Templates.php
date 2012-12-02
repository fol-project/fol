<?php
/**
 * Fol\Templates
 * 
 * A simple class to manage template files 
 */
namespace Fol;

use Fol\Container;

class Templates {
	protected $renders;
	protected $templates;
	protected $templatesPath = array();
	protected $helpers = array();



	/**
	 * Constructor method. You must define the base folder where the templates file are stored
	 * 
	 * @param string/array $paths The base folder paths
	 */
	public function __construct ($paths) {
		$this->addFolders($paths);
	}



	/**
	 * Adds new base folders where search for the templates files
	 * 
	 * @param string/array $paths The base folder paths
	 * @param boolean $prepend If it's true, insert the new folder at begining of the array.
	 */
	public function addFolders ($paths, $prepend = true) {
		$paths = (array)$paths;

		foreach ($paths as &$path) {
			if (substr($path, -1) !== '/') {
				$path .= '/';
			}
		}

		if ($prepend === true) {
			$this->templatesPath = array_merge($paths, $this->templatesPath);
		} else {
			$this->templatesPath = array_merge($this->templatesPath, $paths);
		}
	}



	/**
	 * Register a new helper
	 * 
	 * @param string $name The helper name
	 * @param Closure $helper The function that execute this helper
	 */
	public function setHelper ($name, \Closure $helper) {
		$this->helpers[$name] = $helper;
	}



	/**
	 * Register various helpers
	 * 
	 * @param array $helpers The helpers to register
	 */
	public function setHelpers (array $helpers) {
		$this->helpers = array_replace($this->helpers, $helpers);
	}



	/**
	 * Magic function to execute the registered helpers
	 */
	public function __call ($name, $arguments) {
		if (isset($this->helpers[$name])) {
			return call_user_func_array($this->helpers[$name], $arguments);
		}
	}



	/**
	 * Register a new template file with a name
	 * You can define an array of name => file
	 * 
	 * @param string $name The template name (for example: menu)
	 * @param string $file The file path of the template (for example: menu.php)
	 * @param array $data An optional array of data used in the template. If the array is numerical, renders the template once for each item
	 */
	public function register ($name, $file = null, array $data = null) {
		if (is_array($name)) {
			foreach ($name as $name => $file) {
				$this->templates[$name] = $file;
			}
		} else if ($data === null) {
			$this->templates[$name] = $file;
		} else {
			$this->renders[$name] = $this->render($file, $data);
		}
	}



	/**
	 * Unregister a template file
	 * 
	 * @param string $name The template name
	 */
	public function unregister ($name) {
		unset($this->templates[$name]);
		unset($this->renders[$name]);
	}



	/**
	 * Gets a template file by name or filename
	 * 
	 * $templates->getFile('menu');
	 * $templates->getFile('menu.php');
	 * 
	 * @param string $template The template name or file
	 * 
	 * Returns string The template file path or false if does not exists
	 */
	public function getFile ($template) {
		if (isset($this->templates[$template])) {
			$template = $this->templates[$template];
		}

		foreach ($this->templatesPath as $path) {
			if (is_file($path.$template)) {
				return $path.$template;
			}
		}

		return false;
	}



	/**
	 * Private function that renders a template file and returns its content
	 * 
	 * @param string $_file The file path
	 * @param array $_data An array of variables used in the template.
	 *
	 * @return string The file content
	 */
	protected function renderFile ($_file, array $_data = null) {
		if ($_data !== null) {
			extract((array)$_data, EXTR_SKIP);
		}

		ob_start();

		include($_file);

		return ob_get_clean();
	}



	/**
	 * Render a template and return its content
	 * 
	 * @param string $template The template name or file path
	 * @param array $data An optional array of data used in the template. If the array is numerical, renders the template once for each item
	 *
	 * @return string The template rendered
	 */
	public function render ($template, array $data = null) {
		if (($data === null) && isset($this->renders[$template])) {
			return $this->renders[$template];
		}

		if (($data !== null) && (!$data || isset($data[0]))) {
			$result = '';
			$total = count($data);

			foreach ($data as $index => $value) {
				$value['_index'] = $index;
				$value['_total'] = $total;

				$result .= "\n".$this->render($template, $value, $wrap);
			}

			return $result;
		}

		if (($template = $this->getFile($template)) === false) {
			return false;
		}

		return $this->renderFile($template, $data);
	}
}
?>
