<?php
/*
 * function __autoload ($class_name)
 */
function __autoload ($class_name) {
	$path = explode('\\', $class_name);
	$file = array_pop($path);

	if ($file[0] == 'i') {
		$file = 'interface_'.strtolower(substr($file, 1)).'.php';
	} else {
		$file = $file.'.php';
	}

	if ($path[0] === 'Controllers') {
		array_unshift($path, 'web');
	} else {
		array_splice($path, 1, 0, 'includes');
	}

	$file = implode('/', $path).'/'.$file;

	if (is_file(BASE_PATH.$file)) {
		include_once (BASE_PATH.$file);
	}
}

spl_autoload_register('__autoload');



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