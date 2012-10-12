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
	protected $templatesPath;



	/**
	 * Constructor method. You must define the base folder where the templates file are stored
	 * 
	 * @param string $path The base folder path
	 */
	public function __construct ($path) {
		$this->setFolder($path);
	}



	/**
	 * Defines the base folder where the templates files are stored
	 * 
	 * @param string $path The base folder path
	 */
	public function setFolder ($path) {
		if (substr($path, -1) !== '/') {
			$path .= '/';
		}

		$this->templatesPath = $path;
	}



	/**
	 * Register a new template file with a name
	 * You can define an array of name => file
	 * 
	 * @param string $name The template name (for example: menu)
	 * @param string $file The file path of the template (for example: menu.php)
	 */
	public function register ($name, $file = null) {
		if (is_array($name)) {
			foreach ($name as $name => $file) {
				$this->templates[$name] = $file;
			}
		} else {
			$this->templates[$name] = $file;
		}
	}



	/**
	 * Unregister a template file
	 * 
	 * @param string $name The template name
	 */
	public function unregister ($name) {
		unset($this->templates[$name]);
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

		$template = $this->templatesPath.$template;

		if (is_file($template)) {
			return $template;
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
			extract($_data, EXTR_SKIP);
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


	/**
	 * Register a rendered template to use inside another template
	 * 
	 * @param string $name The rendered template name (for example: menu)
	 * @param string $template The template name or file path
	 * @param array $data An optional array of data used in the template. If the array is numerical, renders the template once for each item
	 */
	public function registerRender ($name, $template, array $data = null) {
		$this->renders[$name] = $this->render($template, $data);
	}


	/**
	 * Unregister a rendered template
	 * 
	 * @param string $name The template name
	 */
	public function unregisterRender ($name) {
		unset($this->renders[$name]);
	}
}
?>
