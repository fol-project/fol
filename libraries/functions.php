<?php
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

	if (is_null($pre)) {
		echo 'NULL';
	} else if (is_bool($pre)) {
		echo $pre ? 'TRUE' : 'FALSE';
	} else {
		ob_start();
		print_r($pre);
		echo htmlspecialchars(ob_get_clean());
	}

	echo '</pre>';
}

function spre ($pre, $info = false) {
	pre((string)$pre, $info);
}