<?php
namespace Fol;

use Fol\Container;

class Views {
	protected $Controller;

	public $views_path;
	public $public_http;



	/**
	 * public function __construct (object $Controller)
	 *
	 * Returns none
	 */
	public function __construct ($Controller) {
		$this->Controller = $Controller;

		$this->views_path = $Controller->App->path.'views/';
		$this->public_http = $Controller->App->public_http;
	}



	/**
	 * public function getFile (string $name)
	 *
	 * Gets a template file by name or filename
	 * Returns string/false
	 */
	public function getFile ($name) {
		if (is_file($this->views_path.$name)) {
			return $this->views_path.$name;
		}

		return false;
	}



	/**
	 * private function renderFile (string $file, [array $data])
	 *
	 * Returns mixed
	 */
	protected function renderFile ($file, $data = array()) {
		if ($data) {
			extract((array)$data, EXTR_SKIP);
		}

		ob_start();

		include($file);

		return ob_get_clean();
	}



	/**
	 * public function render (string $template, [array $data], [string $wrap])
	 * public function render (array $render_name)
	 *
	 * Render a template
	 *
	 * return string/boolean
	 */
	public function render ($template, array $data = null, $wrap = null) {
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

		if (!($template = $this->getFile($template))) {
			return false;
		}

		$result = $this->renderFile($template, $data);

		if ($wrap) {
			return printf($wrap, $result);
		}

		return $result;
	}
}
?>