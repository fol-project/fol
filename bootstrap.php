<?php
use Fol\Loader;
use Fol\Errors;

define('ACCESS_INTERFACE', (php_sapi_name() === 'cli') ? 'cli' : 'http');
define('BASE_PATH', str_replace('\\', '/', __DIR__));

if (ACCESS_INTERFACE === 'cli') {
	$components = [
		'scheme' => 'http',
		'host' => 'localhost',
		'path' => ''
	];

	if (!empty($argv[1]) && preg_match('|^(\{([^\}]+)\})(.*)$|', $argv[1], $match)) {
		$argv[1] = $match[2].$match[3];
		$components = parse_url($match[2]) + $components;
	}
	
	define('BASE_ABSOLUTE_URL', $components['scheme'].'://'.$components['host']);
	define('BASE_URL', $components['path']);

	unset($components, $match);
} else {
	define('BASE_URL', preg_replace('|/+|', '/', strtolower(preg_replace('|^'.str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])).'|i', '', BASE_PATH))));
	define('BASE_ABSOLUTE_URL', ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST']);
}

include(BASE_PATH.'/libs/Fol/Loader.php');

Loader::register();
Loader::setLibrariesPath(BASE_PATH.'/libs');
Loader::registerComposer();

Errors::register();
