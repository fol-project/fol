<?php
/*
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