<?php
namespace Fol\Containers;

class Headers {
	private $items = array();

	static public $formats = array(
		'atom' => array('application/atom+xml'),
		'css' => array('text/css'),
		'html' => array('text/html', 'application/xhtml+xml'),
		'gif' => array('image/gif'),
		'jpg' => array('image/jpeg', 'image/jpg'),
		'js'  => array('application/javascript', 'application/x-javascript', 'text/javascript'),
		'json' => array('application/json', 'application/x-json', 'text/json'),
		'png' => array('image/png',  'image/x-png'),
		'pdf' => array('application/pdf', 'application/x-download'),
		'rdf' => array('application/rdf+xml'),
		'txt' => array('text/plain'),
		'xml' => array('text/xml', 'application/xml', 'application/x-xml'),
		'zip' => array('application/x-zip', 'application/zip', 'application/x-zip-compressed'),
	);

	static public $status = array(
		100 => 'Continue',
		101 => 'Switching Protocols',
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		307 => 'Temporary Redirect',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		418 => 'I\'m a teapot',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
	);



	/**
	 * public static function getFormat ($mimetype)
	 *
	 * Gets the format related with a mimetype
	 * Returns string/false
	 */
	public static function getFormat ($mimetype) {
		foreach (self::$formats as $format => $mimetypes) {
			if (in_array($mimetype, $mimetypes)) {
				return $format;
			}
		}

		return false;
	}



	/**
	 * public static function getMimetype ($format)
	 *
	 * Gets the mimetype related with a format
	 * Returns string/false
	 */
	public static function getMimetype ($format) {
		return self::$formats[$format][0] ?: false;
	}



	/**
	 * public static function getStatusText ($code)
	 *
	 * Gets the status text related with a status code
	 * Returns string/false
	 */
	public static function getStatusText ($code) {
		return self::$status[$code] ?: false;
	}


	/**
	 * public function getHeadersFromServer (array $server)
	 *
	 * Detects header from $_SERVER array
	 * Returns array
	 */
	public static function getHeadersFromServer (array $server) {
		$headers = array();

		foreach ($server as $name => $value) {
			if (substr($name, 0, 5) === 'HTTP_') {
				$headers[str_replace('_', '-', substr($name, 5))] = $value;
				continue;
			}

			if (in_array($name, array('CONTENT_LENGTH', 'CONTENT_MD5', 'CONTENT_TYPE'))) {
				$headers[str_replace('_', '-', $name)] = $value;
			}
		}

		if (isset($server['PHP_AUTH_USER'])) {
			$pass = isset($server['PHP_AUTH_PW']) ? $server['PHP_AUTH_PW'] : '';
			$headers['AUTHORIZATION'] = 'Basic '.base64_encode($server['PHP_AUTH_USER'].':'.$pass);
		}

		return $headers;
	}


	/**
	 * public function __construct (array $parameters)
	 *
	 * Detects request info
	 * Returns none
	 */
	public function __construct (array $parameters = array()) {
		$this->set($parameters);
	}



	/**
	 * public function set (string $name, mixed $value, [boolean $replace])
	 * public function set (array $values, [boolean $replace])
	 *
	 * Sets one parameter
	 * Returns none
	 */
	public function set ($name, $value = true, $replace = true) {
		if (is_array($name)) {
			$replace = $value;

			foreach ($name as $key => $value) {
				$this->set($key, $value, $replace);
			}

			return;
		}

		$name = camelCase($name, true, true);

		if ($replace || !isset($this->items[$name])) {
			$this->items[$name] = $value;
		} else {
			$this->items[$name] = array_merge((array)$this->items[$name], (array)$value);
		}
	}



	/**
	 * public function get (string $name, boolean $first)
	 *
	 * Gets one or all parameters
	 * Returns mixed
	 */
	public function get ($name = null, $first = true) {
		if (func_num_args() === 0) {
			return $this->items;
		}

		$name = camelCase($name, true, true);

		if (is_array($this->items[$name]) && $first) {
			return $this->items[$name][0];
		}

		return $this->items[$name];
	}



	/**
	 * public function getParsed (string $name)
	 *
	 * Gets one parameter parsed
	 * Returns array
	 */
	public function getParsed ($name) {
		return $this->toArray($this->get($name));
	}


	/**
	 * public function setParsed (string $name, array $value)
	 *
	 * Sets one parameter parsed
	 * Returns array
	 */
	public function setParsed ($name, array $value) {
		$this->set($name, $this->toString($value));
	}



	/**
	 * public function getDateTime (string $name, [string $default])
	 *
	 * Gets one parameter as getDateTime object
	 * Returns object/false
	 */
	public function getDateTime ($name, $default = 'now') {
		if ($date = $this->get($name) ?: $default) {
			return \DateTime::createFromFormat(DATE_RFC2822, $date);
		}

		return false;
	}



	/**
	 * public function setDateTime (string $name, DateTime/int/string $Datetime)
	 *
	 * Sets one parameter as Datetime object and returns it
	 * Returns object
	 */
	public function setDateTime ($name, $Datetime) {
		if (is_string($Datetime)) {
			$Datetime = new \DateTime($Datetime);
		} else if (is_int($Datetime)) {
			$timestamp = $Datetime;
			$Datetime = new \DateTime();
			$Datetime->setTimestamp($timestamp);
		}

		$Datetime->setTimezone(new \DateTimeZone('UTC'));
		$this->set($name, $Datetime->format('D, d M Y H:i:s').' GMT');

		return $Datetime;
	}



	/**
	 * public function delete (string $name)
	 *
	 * Deletes one parameter
	 * Returns none
	 */
	public function delete ($name) {
		$name = camelCase($name, true, true);

		unset($this->items[$name]);
	}



	/**
	 * public function clear ()
	 *
	 * Deletes all parameters
	 * Returns none
	 */
	public function clear () {
		$this->items = array();
	}



	/**
	 * public function exists (string $name)
	 *
	 * Checks if a parameter exists
	 * Returns boolean
	 */
	public function exists ($name) {
		$name = camelCase($name, true, true);

		return array_key_exists($name, $this->items);
	}


	/**
	 * public function reset (array $items)
	 *
	 * Reset all items with new values
	 * Returns none
	 */
	public function reset (array $items) {
		$this->clear();
		$this->set($items);
	}


	/**
	 * private function toArray (string $value)
	 *
	 * Parse and return http values
	 * Returns array
	 */
	private function toArray ($value) {
		if (!$value) {
			return array();
		}

		$results = array();

		foreach (explode(',', $value) as $values) {
			$values = explode(';', $values);

			if (strpos($values[0], '=') === false) {
				$name = trim(array_shift($values));
			} else {
				$name = false;
			}

			$parameters = array();

			foreach ($values as $value) {
				list($key, $value) = explode('=', $value, 2);
				$value = trim($value);

				$parameters[trim($key)] = $value ? $value : true;
			}

			if ($name === false) {
				$results[] = $parameters;
			} else {
				$results[$name] = $parameters;
			}
		}

		return $results;
	}



	/**
	 * private function toString (array $values)
	 *
	 * Converts a parsed http value to string
	 * Returns string
	 */
	private function toString (array $values) {
		if (!$values) {
			return '';
		}

		$results = array();

		foreach ($values as $name => $sub_values) {
			$sub_results = array();

			if (!is_int($name)) {
				$sub_results[] = $name;
			}

			foreach ($sub_values as $sub_name => $sub_value) {
				if ($sub_value === false) {
					continue;
				}

				if ($sub_value === true) {
					$sub_results[] = $sub_name;
				} else {
					$sub_results[] = $sub_name.'='.$sub_value;
				}
			}

			$results[] = implode(';', $sub_results);
		}

		return implode(',', $results);
	}



	/**
	 * private function setCache (array $options)
	 *
	 * Set cache configuration
	 * Returns none
	 */
	public function setCache (array $options) {
		if (isset($options['ETag'])) {
			if ($options['ETag']) {
				$this->set('ETag', $options['ETag']);
			} else {
				$this->delete('ETag');
			}
		}

		if (isset($options['Last-Modified'])) {
			if ($options['Last-Modified']) {
				$this->setDateTime('Last-Modified', $options['Last-Modified']);
			} else {
				$this->delete('Last-Modified');
			}
		}

		if (isset($options['Expires'])) {
			if ($options['Expires']) {
				$this->setDateTime('Expires', $options['Expires']);
			} else {
				$this->delete('Expires');
			}
		}

		$cache_control = $this->getParsed('Cache-Control');

		if ($cache_control) {
			$cache_control = current($cache_control);
		}

		if (isset($options['max-age'])) {
			if ($options['max-age']) {
				$cache_control['max-age'] = $options['max-age'];
			} else {
				unset($cache_control['max-age']);
			}
		}

		if (isset($options['s-maxage'])) {
			if ($options['s-maxage']) {
				$cache_control['s-maxage'] = $options['s-maxage'];
			} else {
				unset($cache_control['s-maxage']);
			}
		}

		if (isset($options['must-revalidate'])) {
			if ($options['must-revalidate']) {
				$cache_control['must-revalidate'] = true;
			} else {
				unset($cache_control['must-revalidate']);
			}
		}

		if (isset($options['proxy-revalidate'])) {
			if ($options['proxy-revalidate']) {
				$cache_control['proxy-revalidate'] = true;
			} else {
				unset($cache_control['proxy-revalidate']);
			}
		}

		if (isset($options['no-store'])) {
			if ($options['no-store']) {
				$cache_control['no-store'] = true;
			} else {
				unset($cache_control['no-store']);
			}
		}

		if (isset($options['private'])) {
			$options['public'] = true;
		}

		if (isset($options['public'])) {
			if ($options['public']) {
				$cache_control['public'] = true;
				unset($cache_control['private']);
			} else {
				$cache_control['private'] = true;
				unset($cache_control['public']);
			}
		}

		if ($cache_control) {
			$this->setParsed('Cache-Control', array($cache_control));
		} else {
			$this->delete('Cache-Control');
		}
	}



	/**
	 * private function getCache ()
	 *
	 * Returns the cache configuration
	 * Returns array
	 */
	public function getCache () {
		$cache = current($this->getParsed('Cache-Control'));

		$cache['ETag'] = $this->get('ETag');
		$cache['Last-Modified'] = $this->getDateTime('Last-Modified');
		$cache['Expires'] = $this->getDateTime('Expires');

		return $cache;
	}
}
?>