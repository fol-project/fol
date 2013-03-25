<?php
/**
 * Fol\Http\Headers
 * 
 * Manage http headers
 */
namespace Fol\Http;

class Headers {
	protected $items = array();

	/**
	 * list of standard mime-types used
	 */
	static public $formats = array(
		'atom' => array('application/atom+xml'),
		'css' => array('text/css'),
		'html' => array('text/html', 'application/xhtml+xml'),
		'gif' => array('image/gif'),
		'jpg' => array('image/jpeg', 'image/jpg'),
		'js'  => array('text/javascript', 'application/javascript', 'application/x-javascript'),
		'json' => array('text/json', 'application/json', 'application/x-json'),
		'png' => array('image/png',  'image/x-png'),
		'pdf' => array('application/pdf', 'application/x-download'),
		'rdf' => array('application/rdf+xml'),
		'rss' => array('application/rss+xml'),
		'txt' => array('text/plain'),
		'xml' => array('text/xml', 'application/xml', 'application/x-xml'),
		'zip' => array('application/zip', 'application/x-zip', 'application/x-zip-compressed')
	);


	/**
	 * Magic function to recover the object exported by var_export
	 */
	public static function __set_state ($array) {
		return new static($array['items']);
	}


	/**
	 * Magic function to convert all headers to a string
	 */
	public function __toString () {
		$text = '';

		foreach ($this->items as $name => $value) {
			if (is_string($value)) {
				$text .= "$name: $value\n";
			} else {
				foreach ($value as $value) {
					$text .= "$name: $value\n";
				}
			}
		}

		return $text;
	}



	/**
	 * Gets the format related with a mimetype. Search in self::$formats array.
	 * 
	 * $headers->getFormat('text/css') Returns "css"
	 * 
	 * @param string $mimetype The mimetype to search
	 * 
	 * @return string The extension of the mimetype or false
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
	 * Gets the mimetype related with a format. This is the opposite of getFormat()
	 * 
	 * $headers->getMimetype('css') Returns "text/css"
	 * 
	 * @param string $format The format to search
	 * 
	 * @return string The mimetype code or false
	 */
	public static function getMimetype ($format) {
		return isset(self::$formats[$format][0]) ? self::$formats[$format][0] : false;
	}



	/**
	 * Constructor function. You can set parameters
	 * 
	 * @param $parameters Data to save
	 */
	public function __construct (array $parameters = array()) {
		if ($parameters) {
			$this->set($parameters);
		}
	}


	/**
	 * Normalize the name of the parameters.
	 * $headers->normalize('CONTENT type') Returns "Content-Type"
	 * 
	 * @param string $string The text to normalize
	 * 
	 * @return string The normalized text
	 */
	private function normalize ($string) {
		return str_replace(' ', '-', ucwords(strtolower(str_replace('-', ' ', $string))));
	}



	/**
	 * Sends the headers if don't have been send by the developer
	 * 
	 * @return boolean True if headers has been sent and false if headers had been sent before
	 */
	public function send () {
		if (headers_sent()) {
			return false;
		}

		foreach ($this->items as $name => $value) {
			if (is_array($value)) {
				foreach ($value as $value) {
					header($name.': '.$value, false);
				}
			} else {
				header($name.': '.$value, false);
			}
		}

		return true;
	}



	/**
	 * Stores new headers. You can define an array to store more than one at the same time
	 * 
	 * @param string $name The header name
	 * @param string $value The header value
	 * @param boolean $replace True to replace a previous header with the same name
	 */
	public function set ($name, $value = true, $replace = true) {
		if (is_array($name)) {
			$replace = $value;

			foreach ($name as $name => $value) {
				$this->set($name, $value, $replace);
			}

			return;
		}

		$name = $this->normalize($name);

		if ($replace || !isset($this->items[$name])) {
			$this->items[$name] = $value;
		} else {
			$this->items[$name] = array_merge((array)$this->items[$name], (array)$value);
		}
	}



	/**
	 * Gets one or all parameters
	 * 
	 * @param string $name The header name
	 * @param boolean $first Set true to return just the value of the first header with this name. False to return an array with all values. 
	 * 
	 * @return string The header value or an array with all values
	 */
	public function get ($name = null, $first = true) {
		if (func_num_args() === 0) {
			return $this->items;
		}

		$name = $this->normalize($name);

		if (!isset($this->items[$name])) {
			return null;
		}

		if (is_array($this->items[$name]) && $first) {
			return $this->items[$name][0];
		}

		return $this->items[$name];
	}



	/**
	 * Gets the value of an header parsed.
	 * 
	 * $header->get('Accept') Returns: text/html,application/xhtml+xml,application/xml;q=0.9,* /*;q=0.8
	 * $header->getParsed('Accept')
	 * Array (
	 *     [text/html] => Array()
	 *     [application/xhtml+xml] => Array()
	 *     [application/xml] => Array([q] => 0.9)
	 *     [* /*] => Array([q] => 0.8)
	 * )
	 * 
	 * @param string $name The header name
	 * 
	 * @return array The parsed value
	 */
	public function getParsed ($name) {
		return $this->toArray($this->get($name));
	}


	/**
	 * It's the opposite of getParsed: saves a header defining the value as array
	 * 
	 * @param string $name The header name
	 * @param array $value The parsed value
	 */
	public function setParsed ($name, array $value) {
		$this->set($name, $this->toString($value));
	}



	/**
	 * Gets one parameter as a getDateTime object
	 * Useful for datetime values (Expires, Last-Modification, etc)
	 * 
	 * @param string $name The header name
	 * @param string $default The default value if the header does not exists
	 * 
	 * @return Datetime The value in a datetime object or false
	 */
	public function getDateTime ($name, $default = 'now') {
		if ($date = $this->get($name) ?: $default) {
			return \DateTime::createFromFormat(DATE_RFC2822, $date);
		}

		return false;
	}



	/**
	 * Define a header using a Datetime object and returns it
	 * 
	 * @param string $name The header name
	 * @param Datetime|string $Datetime The datetime object. You can define also an string so the Datetime object will be created
	 * 
	 * @return Datetime The datetime object
	 */
	public function setDateTime ($name, $Datetime) {
		if (is_string($Datetime)) {
			$Datetime = new \DateTime($Datetime);
		}

		$Datetime->setTimezone(new \DateTimeZone('UTC'));
		$this->set($name, $Datetime->format('D, d M Y H:i:s').' GMT');

		return $Datetime;
	}



	/**
	 * Deletes one or all headers
	 * 
	 * $headers->delete('content-type') Deletes one header
	 * $headers->delete() Deletes all headers
	 * 
	 * @param $name The header name
	 */
	public function delete ($name = null) {
		if (func_num_args() === 0) {
			$this->items = array();
		} else {
			$name = $this->normalize($name);

			unset($this->items[$name]);
		}
	}



	/**
	 * Checks if a header exists
	 * 
	 * @param string $name The header name
	 * 
	 * @return boolean True if the header exists, false if not
	 */
	public function exists ($name) {
		$name = $this->normalize($name);

		return array_key_exists($name, $this->items);
	}



	/**
	 * Private function to parse and return http values
	 * 
	 * @param string $value The string to parse
	 * 
	 * @return array The parsed value
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
	 * Private function to convert a parsed http value to string
	 * 
	 * @param array $values The parsed value
	 * 
	 * @return string The value in string format
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
}
?>
