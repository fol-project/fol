<?php
namespace Fol;

use Fol\Container;

class Templates {
	protected $Controller;
	protected $templates;
	protected $templatesPath;



	/**
	 * public function __construct (string $path)
	 *
	 * Returns none
	 */
	public function __construct ($path) {
		$this->setFolder($path);
	}



	/**
	 * public function unregister (string $name)
	 *
	 * Deletes a service
	 * Returns none
	 */
	public function setFolder ($path) {
		if (substr($path, -1) !== '/') {
			$path .= '/';
		}

		$this->templatesPath = $path;
	}



	/**
	 * public function register (string $name, string $file)
	 *
	 * Register a new template
	 * Returns none
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
	 * public function unregister (string $name)
	 *
	 * Deletes a service
	 * Returns none
	 */
	public function unregister ($name) {
		unset($this->templates[$name]);
	}



	/**
	 * public function getFile (string $template)
	 *
	 * Gets a template file by name or filename
	 * Returns string/false
	 */
	public function getFile ($template) {
		if (isset($this->templates[$template])) {
			$template = $this->templates[$template];
		}

		$template = ($template[0] === '/') ? $template : $this->templatesPath.$template;

		if (is_file($template)) {
			return $template;
		}

		echo $template.'<br>';

		return false;
	}



	/**
	 * private function renderFile (string $_file, [array $_data])
	 *
	 * Returns mixed
	 */
	protected function renderFile ($_file, array $_data = null) {
		if (isset($_data)) {
			extract((array)$_data, EXTR_SKIP);
		}

		ob_start();

		include($_file);

		return ob_get_clean();
	}



	/**
	 * public function render (string $template, [array $data])
	 *
	 * Render a template
	 *
	 * return string/boolean
	 */
	public function render ($template, array $data = null) {
		if (is_array($data) && (!$data || preg_match('/^[0-9]+$/', implode(array_keys($data))))) {
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
