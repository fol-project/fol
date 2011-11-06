<?php
namespace Fol;

class Input {
	public $actions;
	public $format;
	public $language;
	public $vars;
	public $get;
	public $post;



	/**
	 * public function __construct (void)
	 *
	 * Detects request info
	 * Returns none
	 */
	public function __construct () {
		global $Config;

		$get = (array)filter_input_array(INPUT_GET);
		$post = $this->arrayMerge($this->arrayFiles(), (array)filter_input_array(INPUT_POST));

		unset($_GET, $_POST, $_FILES);

		$this->get = array_keys($get);
		$this->post = array_keys($post);
		$this->vars = $this->arrayMerge($get, $post);

		$config = $Config->get('scene', 'scene');

		$this->actions = $this->detectActions($config['actions_variable']);
		$this->language = $this->detectLanguage($config);
		$this->format = $this->detectFormat();
	}



	/**
	 * private function arrayFiles (void)
	 *
	 * Fix the order data of the array $_FILES
	 * Returns array
	 */
	private function arrayFiles () {
		if (!$_FILES) {
			return array();
		}

		$array_files = array();

		foreach ($_FILES as $name => $values) {
			if (!is_array(current($values))) {
				$array_files[$name] = $values;

				continue;
			}

			foreach ($values as $type_info => $info) {
				$array_files[$name] = $this->arrayMerge($array_files[$name], $this->_arrayFiles($info, $type_info));
			}
		}

		return $array_files;
	}


	/**
	 * private function _arrayFiles (void)
	 *
	 * To execute recursively from arrayFiles
	 * Returns array
	 */
	private function _arrayFiles ($array, $last) {
		$return = array();

		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$return[$key] = $this->_arrayFiles($value, $last);
			} else {
				$return[$key][$last] = $value;
			}
		}

		return $return;
	}



	/**
	 * private function detectActions (string $variable)
	 *
	 * Returns the current actions
	 * Return array
	 */
	private function detectActions ($variable) {
		$actions = array();

		foreach ((array)$this->vars[$variable] as $name => $value) {
			if (is_int($name)) {
				$name = $value;
				$value = null;
			}

			$name = trim($name);

			if ($name) {
				$actions[$name] = $value;
			}
		}

		$this->delete($variable);

		return $actions;
	}




	/**
	 * private function detectLanguage (array $config)
	 *
	 * Detects the current language
	 * Returns string/false
	 */
	private function detectLanguage ($config) {
		$config = $config['languages'];
		$languages = $config['availables'];

		if (!$languages) {
			return false;
		}

		$cookie = $this->scene.'-'.$this->module.'-language';
		$duration = 3600*24*365;


		//Language detection
		$language = '';

		switch($config['detection']) {
			case 'subfolder':
				if ($languages[$this->path[0]]) {
					$language = array_shift($this->path);
				}
				break;

			case 'get':
				if ($languages[$this->vars['lang']]) {
					$language = $this->vars['lang'];
					$this->delete('lang');
				}
				break;

			case 'subdomain':
				if ($languages[$this->subdomains[0]]) {
					$language = array_shift($this->subdomains);
				}
				break;
		}

		if (($language = $config['default']) && $languages[$language]) {
			return $language;
		}

		foreach ($this->getLanguages() as $language) {
			if ($languages[$language]) {
				return $language;
			}
		}

		foreach ($languages as $language => $available) {
			if ($available) {
				return $language;
			}
		}

		return false;
	}



	/**
	 * private function detectFormat (void)
	 *
	 * Detects the requested format
	 * Returns string/false
	 */
	private function detectFormat () {
		global $Router;

		if (!$Router->path) {
			return false;
		}

		if (strpos(end($Router->path), '.') === false) {
			return false;
		}

		$info = pathinfo(array_pop($Router->path));

		$Router->path[] = $info['filename'];

		return $info['extension'];
	}



	/**
	 * public function get (string $name, [string $filter])
	 *
	 * Get (and optionally filter) a variable value
	 * Returns mixed
	 */
	public function get ($name, $filter = null) {
		if ($filter) {
			$filter = $this->getSanitizeFilter($filter);
		}

		if (strpos($name, '[') && strpos($name, ']')) {
			$subarrays = explode('[', str_replace(']', '', $name));
			$value = $this->vars;

			while ($subarrays) {
				$value = $value[array_shift($subarrays)];
			}

			if (isset($value) && $filter) {
				return filter_var($value, $filter[0], $filter[1]);
			}
		}

		if (isset($this->vars[$name]) && $filter) {
			return filter_var($this->vars[$name], $filter[0], $filter[1]);
		}
	}



	/**
	 * public function set (string $name, mixed $value)
	 *
	 * Changes or creates a new variable
	 * Returns none
	 */
	public function set ($name, $value) {
		$this->vars[$name] = $value;
	}



	/**
	 * public function delete (string $name)
	 *
	 * Deletes a variable
	 * Returns none
	 */
	public function delete ($name) {
		unset($this->vars[$name]);
	}



	/**
	 * private function getSanitizeFilter ($name)
	 *
	 * Returns array
	 */
	private function getSanitizeFilter ($name) {
		switch ($name) {
			case 'string':
			return array(FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);

			case 'int':
			return array(FILTER_SANITIZE_NUMBER_INT);

			case 'bool':
			return array(FILTER_VALIDATE_BOOLEAN);

			case 'float':
			return array(FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

			case 'special_chars':
			return array(FILTER_SANITIZE_SPECIAL_CHARS);

			case 'encoded':
			return array(FILTER_SANITIZE_ENCODED);

			case 'url':
			return array(FILTER_SANITIZE_URL);

			case 'email':
			return array(FILTER_SANITIZE_EMAIL);
		}

		return array(FILTER_UNSAFE_RAW);
	}



	/*
	 * private function arrayMerge (array $array1, [array $array2], ...)
	 *
	 * Merge two or more arrays, replacing their values
	 * Returns array
	 */
	private function arrayMerge () {
		$params = func_get_args();

		$return = array_shift($params);

		foreach ($params as $array) {
			if (!is_array($array)) {
				continue;
			}

			foreach ($array as $key => $value) {
				if (isset($return[$key]) && is_array($value) && is_array($return[$key])) {
					$return[$key] = $this->arrayMerge($return[$key], $value);
				} else {
					$return[$key] = $value;
				}
			}
		}

		return $return;
	}



	/**
	 * public function getLanguages (void)
	 *
	 * Returns the browser accepted languages
	 * Returns array
	 */
	public function getLanguages () {
		if (!$_SERVER['HTTP_ACCEPT_LANGUAGE']) {
			return array();
		}

		$browser = explode(',', str_replace(array(' ', 'q='), '', strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE'])));
		$languages = array();

		foreach ($browser as $language) {
			list($language, $q) = explode(';', $language);

			$q = is_null($q) ? 1 : $q;

			if (strstr($language, '-')) {
				$language = explode('-', $language);

				if (!$languages[$language[1]]) {
					$languages[$language[1]] = $q;
				}

				if (!$languages[$language[0]]) {
					$languages[$language[0]] = $q;
				}
			} else {
				$languages[$language] = $q;
			}
		}

		arsort($languages, SORT_NUMERIC);

		return array_keys($languages);
	}



	/**
	 * public function getIp ()
	 *
	 * Gets the real client ip
	 * Returns string
	 */
	public function getIp () {
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			return $_SERVER['HTTP_CLIENT_IP'];
		} else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			return $_SERVER['REMOTE_ADDR'];
		}
	}



	/**
	 * public function getScheme ()
	 *
	 * Gets the request scheme
	 * Returns string
	 */
	public function getScheme () {
		return ($_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
	}



	/**
	 * public function isAjax ()
	 *
	 * Detects if the request has been made by ajax or not
	 * Returns boolean
	 */
	public function isAjax () {
		return ($_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') ? true : false;
	}
}
?>