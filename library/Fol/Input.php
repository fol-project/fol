<?php
namespace Fol;

class Input {
	public $format;
	public $language;
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

		$this->get = (array)filter_input_array(INPUT_GET);

		if ($_FILES) {
			$this->post = $this->arrayMerge($this->arrayFiles(), (array)filter_input_array(INPUT_POST));
		} else {
			$this->post = (array)filter_input_array(INPUT_POST);
		}

		unset($_GET, $_POST, $_FILES);

		$config = $Config->get('scene', 'scene');

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


		//Language detection
		$language = '';

		switch($config['detection']) {
			case 'subfolder':
				global $Router;

				if ($Router->path && $languages[$Router->path[0]]) {
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

		foreach ($this->httpAcceptLanguages() as $language) {
			if ($languages[$language['short']]) {
				return $language['short'];
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

		if (!$Router->path || (strpos(end($Router->path), '.') === false)) {
			$accept = current($this->httpAccept());

			return $accept ? $accept['short'] : false;
		}

		$info = pathinfo(array_pop($Router->path));

		$Router->path[] = $info['filename'];

		return $info['extension'];
	}



	/**
	 * public function get ([string $name], [string $filter])
	 *
	 * Get (and optionally filter) a variable value via GET
	 * Returns mixed
	 */
	public function get ($name = null, $filter = null) {
		if (is_null($name)) {
			return $this->get;
		}

		if ($filter) {
			$filter = $this->getSanitizeFilter($filter);
		}

		if (isset($this->get[$name])) {
			return $filter ? filter_var($this->get[$name], $filter[0], $filter[1]) : $this->get[$name];
		}

		if (strpos($name, '[') && strpos($name, ']')) {
			$subarrays = explode('[', str_replace(']', '', $name));
			$value = $this->get;

			while ($subarrays) {
				$value = $value[array_shift($subarrays)];
			}

			if (isset($value)) {
				return $filter ? filter_var($value, $filter[0], $filter[1]) : $value;
			}
		}
	}



	/**
	 * public function post ([string $name], [string $filter])
	 *
	 * Get (and optionally filter) a variable value via POST
	 * Returns mixed
	 */
	public function post ($name = null, $filter = null) {
		if (is_null($name)) {
			return $this->post;
		}

		if ($filter) {
			$filter = $this->getSanitizeFilter($filter);
		}

		if (isset($this->post[$name])) {
			return $filter ? filter_var($this->post[$name], $filter[0], $filter[1]) : $this->post[$name];
		}

		if (strpos($name, '[') && strpos($name, ']')) {
			$subarrays = explode('[', str_replace(']', '', $name));
			$value = $this->post;

			while ($subarrays) {
				$value = $value[array_shift($subarrays)];
			}

			if (isset($value)) {
				return $filter ? filter_var($value, $filter[0], $filter[1]) : $value;
			}
		}
	}



	/**
	 * public function postGet ([string $name], [string $filter])
	 *
	 * Get (and optionally filter) a variable value via POST or GET
	 * Returns mixed
	 */
	public function postGet ($name = null, $filter = null) {
		if (is_null($name)) {
			return $this->arrayMerge($this->get, $this->post);
		}

		$variable = $this->post($name, $filter);

		if (is_null($variable)) {
			return $this->get($name, $filter);
		}

		return $variable;
	}



	/**
	 * public function delete (string $name)
	 *
	 * Deletes a variable in GET and POST
	 * Returns none
	 */
	public function delete ($name) {
		unset($this->get[$name], $this->post[$name]);
	}



	/**
	 * public function exists (string $name)
	 *
	 * Check if a variable is set in GET or POST
	 * Returns boolean
	 */
	public function exists ($name) {
		return (isset($this->get[$name]) || isset($this->post[$name])) ? true : false;
	}



	/**
	 * public function cookie (string $name, [string $filter])
	 *
	 * Get (and optionally filter) a cookie value
	 * Returns mixed
	 */
	public function cookie ($name, $filter = null) {
		if ($filter) {
			$filter = $this->getSanitizeFilter($filter);

			return filter_input(INPUT_COOKIE, $name, $filter[0], $filter[1]);
		}

		return filter_input(INPUT_COOKIE, $name);
	}



	/**
	 * public function setCookie (string $name, [string $value], [int $expire])
	 *
	 * Returns boolean
	 */
	public function setCookie ($name, $value = '', $expire = 0) {
		$defaults = array(
			'path' => BASE_WWW,
			'domain' => getenv('SERVER_NAME'),
			'secure' => false,
			'httponly' => false
		);

		if (!is_array($name)) {
			$name = array(
				'name' => $name,
				'value' => $value,
				'expire' => $expire
			);
		}

		$name += $defaults;

		if ($name['expire']) {
			$name['expire'] += time();
		}

		return setcookie($name['name'], $name['value'], $name['expire'], $name['path'], $name['domain'], $name['secure'], $name['httponly']);
	}



	/**
	 * public function deleteCookie (string $name)
	 *
	 * Returns boolean
	 */
	public function deleteCookie ($name) {
		return setcookie($name, '', 1, BASE_WWW);
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
	 * public function httpAcceptLanguages (void)
	 *
	 * Returns the browser accept languages header
	 * Returns array
	 */
	public function httpAcceptLanguages () {
		if (!$_SERVER['HTTP_ACCEPT_LANGUAGE']) {
			return array();
		}

		preg_match_all('#(([\w]+)\-?([^,;/]+)?)(;q=([0-9\.]+))?#', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches, PREG_SET_ORDER);

		$languages = array();

		foreach ($matches as $match) {
			$languages[] = array(
				'q' => isset($match[5]) ? floatval($match[5]) : 1,
				'language' => $match[1],
				'short' => $match[2]
			);
		}

		return $languages;
	}



	/**
	 * public function httpAccept (void)
	 *
	 * Returns the http accept header
	 * Returns array
	 */
	public function httpAccept () {
		if (!$_SERVER['HTTP_ACCEPT']) {
			return array();
		}

		preg_match_all('#(([\w]+)/([^,;/]+))(;q=([0-9\.]+))?#', $_SERVER['HTTP_ACCEPT'], $matches, PREG_SET_ORDER);

		$formats = array();

		foreach ($matches as $match) {
			$formats[] = array(
				'q' => isset($match[5]) ? floatval($match[5]) : 1,
				'mimetype' => $match[1],
				'short' => $match[3]
			);
		}

		return $formats;
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