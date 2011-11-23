<?php
namespace Fol;

class Templates {
	private $templates;
	private $renders;



	/**
	 * public function set (string $name, mixed $value, [string $exit_mode])
	 * public function set (array $values, [string $exit_mode])
	 *
	 * Set a name to a template file
	 * Returns none
	 */
	public function set ($name, $value = '', $exit_mode = '') {
		if (is_array($name)) {
			foreach ($name as $n => $v) {
				$this->templates[$n][$value] = $v;
			}

			return;
		}

		$this->templates[$name][$exit_mode] = $value;
	}



	/**
	 * public function get (string $name, [string $exit_mode])
	 *
	 * Gets a template file by name or filename
	 * Returns mixed/false
	 */
	public function get ($name, $exit_mode = null) {
		$path = SCENE_PATH.'templates/';

		if (!$this->templates[$name]) {
			return is_file($path.$name) ? $path.$name : false;
		}

		if (is_null($exit_mode)) {
			global $Router;

			$exit_mode = $Router->exit_mode;
		}

		if ($template = $this->templates[$name]) {
			if ($template[$exit_mode]) {
				return is_file($path.$template[$exit_mode]) ? $path.$template[$exit_mode] : false;
			}

			if ($template['']) {
				return is_file($path.$template['']) ? $path.$template[''] : false;
			}
		}

		return false;
	}



	/**
	 * public function render (string $template, [array $data], [string $wrap])
	 * public function render (array $render_settings)
	 * public function render (array $render_name)
	 *
	 * Render a template
	 *
	 * return string/boolean
	 */
	public function render ($render_settings, $data = null, $wrap = null) {
		if (is_string($render_settings)) {
			if (isset($this->renders[$render_settings])) {
				return $this->renders[$render_settings];
			}

			$render_settings = array(
				'template' => $render_settings,
				'data' => $data
			);
		}

		if (!($render_settings['template'] = $this->get($render_settings['template']))) {
			return false;
		}

		if (is_array($render_settings['data']) && (isNumericalArray($render_settings['data']) || !$render_settings['data'])) {
			$result = '';
			$total = count($render_settings['data']);

			foreach ($render_settings['data'] as $index => $data) {
				if ($render_settings['common_data']) {
					$data += (array)$render_settings['common_data'];
				}

				$data['_index'] = $index;
				$data['_total'] = $total;

				if ($render_settings['wrap']) {
					$result .= "\n".printf($render_settings['wrap'], $this->includeFile($render_settings['template'], $data));
				} else {
					$result .= "\n".$this->includeFile($render_settings['template'], $data);
				}
			}

			return $result;
		}

		if ($render_settings['common_data']) {
			$render_settings['data'] += (array)$render_settings['common_data'];
		}

		$result = $this->includeFile($render_settings['template'], $render_settings['data']);

		if ($render_settings['wrap']) {
			return printf($render_settings['wrap'], $result);
		}

		return $result;
	}



	/**
	 * public function setRender (string $render_name, string $template, [array $data], [string $wrap])
	 * public function setRender (string $render_name, array $render_settings)
	 *
	 * Set a name to a template file
	 * Returns none
	 */
	public function setRender ($render_name, $render_settings, $data = null, $wrap = null) {
		$this->renders[$render_name] = $this->render($render_settings, $data, $wrap);
	}



	/**
	 * private function includeFile (string $file, [array $data])
	 *
	 * Returns mixed
	 */
	private function includeFile ($file, $data = array()) {
		if ($data) {
			extract((array)$data, EXTR_SKIP);
		}

		ob_start();

		include($file);

		return ob_get_clean();
	}
}
?>