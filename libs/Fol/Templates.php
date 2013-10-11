<?php
/**
 * Fol\Templates
 * 
 * A simple class to manage template files 
 */
namespace Fol;

use Fol\Container;

class Templates {
	protected $renders = [];
	protected $templates = [];
	protected $templatesPaths = [];
	protected $currentPath;



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
			$this->templatesPaths = array_merge($paths, $this->templatesPaths);
		} else {
			$this->templatesPaths = array_merge($this->templatesPaths, $paths);
		}
	}



	/**
	 * Register a new template file with a name
	 * You can define an array of name => file
	 * 
	 * @param string $name The template name (for example: menu)
	 * @param string $file The file path of the template (for example: menu.php)
	 */
	public function registerFile ($name, $file = null) {
		$this->templates[$name] = $file;
	}



	/**
	 * Render a file and save the result
	 * 
	 * @param string $name The template name (for example: menu)
	 * @param string $file The file path of the template (for example: menu.php)
	 * @param array $data An optional array of data used in the template. If the array is numerical, renders the template once for each item
	 */
	public function registerRender ($name, $file = null, array $data = null) {
		$this->renders[$name] = $this->render($file, $data);
	}



	/**
	 * Gets a template file by name or filename
	 * 
	 * $templates->file('menu');
	 * $templates->file('menu.php');
	 * 
	 * @param string $template The template name or file
	 * 
	 * Returns string The template file path or false if does not exists
	 */
	public function file ($template) {
		if (isset($this->templates[$template])) {
			$template = $this->templates[$template];
		}

		if (($template[0] !== '/') && !empty($this->currentPath) && is_file($this->currentPath.'/'.$template)) {
			return $this->currentPath.'/'.$template;
		}

		foreach ($this->templatesPaths as $path) {
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
		$_previousPath = $this->currentPath;
		$this->currentPath = dirname($_file);

		if ($_data !== null) {
			extract((array)$_data, EXTR_SKIP);
		}

		unset($_data);

		ob_start();

		include($_file);

		$this->currentPath = $_previousPath;

		return ob_get_clean();
	}



	/**
	 * Render a template and return its content
	 * 
	 * @param string $template The template name or file path
	 * @param array/Iterator/IteratorAggregate $data An optional array of object extending Iterator/IteratorAggregate data used in the template. If the array is numerical or the object extends Iterator/IteratorAggregate interfaces, renders the template once for each item
	 *
	 * @return string The template rendered
	 */
	public function render ($template, $data = null) {
		if (($data === null) && isset($this->renders[$template])) {
			return $this->renders[$template];
		}

		if (($data !== null) && static::isIterable($data)) {
			$result = '';

			foreach ($data as $value) {
				$result .= "\n".$this->render($template, $value, $wrap);
			}

			return $result;
		}

		if (($file = $this->file($template)) === false) {
			throw new \Exception("The template $template does not exists");
		}

		return $this->renderFile($file, $data);
	}


	/**
	 * Simple method to detect if a value must be iterabled or not
	 */
	private static function isIterable ($items) {
		if (is_array($items)) {
			return (empty($items) || isset($data[0]));
		}

		if (($items instanceof \Iterator) || ($items instanceof \IteratorAggregate)) {
			return true;
		}

		return false;
	}
}
