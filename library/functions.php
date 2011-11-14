<?php

/**
 * function __autoload ($class_name)
 */
function __autoload ($class_name) {
	$path = explode('\\', $class_name);
	$file = array_pop($path);

	if ($path[0] === 'Fol') {
		$file = BASE_PATH.'library/'.implode('/', $path).'/'.$file.'.php';
	} else {
		$file = SCENE_PATH.implode('/', $path).'/'.$file.'.php';
	}

	if (is_file($file)) {
		include_once($file);
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