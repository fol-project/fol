<?php
use Fol\Loader;
use Fol\Errors;

define('ACCESS_INTERFACE', (php_sapi_name() === 'cli') ? 'cli' : 'http');
define('BASE_PATH', str_replace('\\', '/', __DIR__));

if (ACCESS_INTERFACE === 'cli') {
	$documentRoot = dirname(__DIR__);

	if (isset($argv[1]) && (strpos($argv[1], '://') !== false) && (($components = parse_url($argv[1])) !== false)) {
		define('BASE_ABSOLUTE_URL', $components['scheme'].'://'.$components['host']);
		unset($components);
	} else {
		define('BASE_ABSOLUTE_URL', 'http://localhost');
	}
} else {
	$documentRoot = realpath($_SERVER['DOCUMENT_ROOT']);
	define('BASE_ABSOLUTE_URL', ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST']);
}

define('BASE_URL', preg_replace('|/+|', '/', strtolower(preg_replace('|^'.str_replace('\\', '/', $documentRoot).'|i', '', BASE_PATH))));
unset($documentRoot);

include(BASE_PATH.'/libs/Fol/Loader.php');

Loader::register();
Loader::setLibrariesPath(BASE_PATH.'/libs');
Loader::registerComposer();

Errors::register();
