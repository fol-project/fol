<?php

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
 * function pre ($value)
 *
 * Throw a exception object
 * Returns false
 */
function pre ($pre) {
	echo '<pre>';
	
	echo '<strong>'.gettype($pre)."</strong>\n";

	echo '<em>';
	debug_print_backtrace();
	echo '</em>'."\n";

	if (is_bool($pre)) {
		echo $pre ? 'TRUE' : 'FALSE';
	} else {
		print_r($pre);
	}

	echo '</pre>';
}