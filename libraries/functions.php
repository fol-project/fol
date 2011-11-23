<?php

/**
 * function __autoload ($class_name)
 */
function __autoload ($class_name) {
	$path = explode('\\', $class_name);
	$file = array_pop($path);
	$file = implode('/', $path).'/'.$file.'.php';

	if (is_file(BASE_PATH.'libraries/'.$file)) {
		include_once(BASE_PATH.'libraries/'.$file);
	} else if (is_file(SCENE_PATH.$file)) {
		include_once(SCENE_PATH.$file);
	}
}

spl_autoload_register('__autoload');



/**
 * function camelCase (string $string, [boolean $upper_first])
 *
 * Transform a string "my-string" to camelCase: "myString"
 * Returns string
 */
function camelCase ($string, $upper_first = false) {
	$string = str_replace('-', ' ', $string);
	$string = str_replace(' ', '', ucwords($string));

	if (!$upper_first) {
		return lcfirst($string);
	}

	return $string;
}



/**
 * function explodeTrim (string $delimiter, string $text, [int $limit])
 *
 * Explode a string and returns only the non-empty elements
 * Returns array
 */
function explodeTrim ($delimiter, $text, $limit = null) {
	$return = array();

	$explode = is_null($limit) ? explode($delimiter, $text) : explode($delimiter, $text, $limit);

	foreach ($explode as $text_value) {
		$text_value = trim($text_value);

		if ($text_value !== '') {
			$return[] = $text_value;
		}
	}

	return $return;
}



/**
 * function isNumericalArray (array $array)
 *
 * Returns true if the array is numerical or false if it's associative
 * Returns boolean
 */
function isNumericalArray ($array) {
	if (is_array($array)) {
		return preg_match('/^[0-9]+$/', implode(array_keys($array)));
	} else {
		return false;
	}
}



/**
 * function exception ([string $message], [int $code])
 *
 * Throw a exception object
 * Returns false
 */
function exception ($message = '', $code = 500) {
	throw new \Fol\Exception($message, $code);

	return false;
}



/**
 * function path (string $path)
 *
 * Returns an absolute path
 * Returns string
 */
function path ($path) {
	if ($path[0] === '/') {
		return BASE_PATH.substr($path, 1);
	}

	return SCENE_PATH.$path;
}



/**
 * function url (string $url)
 *
 * Returns an absolute url
 * Returns string
 */
function url ($path) {
	if ($path[0] === '/') {
		return BASE_HTTP.substr($path, 1);
	}

	return BASE_HTTP.$path;
}



/*
 * function arrayMergeReplaceRecursive (array $array1, array $array2, [array $array3, ...])
 *
 * Merge two arrays recursively replacing the repeated values
 * Returns array
 */
function arrayMergeReplaceRecursive () {
	$params = func_get_args();

	$return = array_shift($params);

	foreach ($params as $array) {
		if (!is_array($array)) {
			continue;
		}

		foreach ($array as $key => $value) {
			if (is_numeric($key) && (!in_array($value, $return))) {
				if (is_array($value)) {
					$return[] = arrayMergeReplaceRecursive($return[$$key], $value);
				} else {
					$return[] = $value;
				}
			} else {
				if (isset($return[$key]) && is_array($value) && is_array($return[$key])) {
					$return[$key] = arrayMergeReplaceRecursive($return[$key], $value);
				} else {
					$return[$key] = $value;
				}
			}
		}
	}

	return $return;
}



/**
 * function pre ($value)
 *
 * Throw a exception object
 * Returns false
 */
function pre ($pre) {
	echo '<pre>';
	print_r($pre);
	echo '</pre>';
}