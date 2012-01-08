<?php
namespace Fol;

class Views {
	public $Templates;

	protected $App;
	protected $public;



	/**
	 * public function __construct (object $App)
	 *
	 * Returns none
	 */
	public function __construct ($App) {
		$this->App = $App;
		$this->Templates = new Containers\Container;
		$this->public_http = $this->App->real_http.'public/';
	}



	/**
	 * public function getFile (string $name)
	 *
	 * Gets a template file by name or filename
	 * Returns string/false
	 */
	public function getFile ($name) {
		$path = $this->App->path.'views/';

		if (is_file($path.$name)) {
			return $path.$name;
		}

		if ($template_file = $this->Templates->get($name) && is_file($path.$template_file)) {
			return $path.$template_file;
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