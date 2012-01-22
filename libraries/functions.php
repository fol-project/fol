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
 * function pre ($value)
 *
 * Prints a debug value in a <pre> html tag
 * Returns false
 */
function pre ($pre, $info = false) {
	echo '<pre>';

	if ($info) {
		echo '<strong>'.gettype($pre)."</strong>\n";

		echo '<em>';
		debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		echo '</em>'."\n";
	}

	if (is_bool($pre)) {
		echo $pre ? 'TRUE' : 'FALSE';
	} else {
		ob_start();
		print_r($pre);
		echo htmlspecialchars(ob_get_clean());
	}

	echo '</pre>';
}