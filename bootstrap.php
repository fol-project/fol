<?php
use Fol\Loader;

define('ACCESS_INTERFACE', (php_sapi_name() === 'cli') ? 'cli' : 'http');
define('BASE_PATH', str_replace('\\', '/', __DIR__));

//Environment variables
include 'environment.php';

define('ENVIRONMENT', getenv('FOL_ENVIRONMENT') ?: 'development');

if (ACCESS_INTERFACE === 'cli') {
	define('BASE_HOST', getenv('FOL_BASE_HOST') ?: 'http://localhost');
	define('BASE_URL', getenv('FOL_BASE_URL') ?: '');
} else {
	define('BASE_HOST', getenv('FOL_BASE_HOST') ?: ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST']);
	define('BASE_URL', getenv('FOL_BASE_URL') ?: preg_replace('|/+|', '/', strtolower(preg_replace('|^'.str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])).'|i', '', BASE_PATH))));
}

//Init the loader (installed with composer or not)
if (is_file(BASE_PATH.'/libs/fol/core/Fol/Loader.php')) {
	require_once BASE_PATH.'/libs/fol/core/Fol/Loader.php';
} else {
	require_once BASE_PATH.'/libs/Fol/Loader.php';
}

Loader::register();
Loader::setLibrariesPath(BASE_PATH.'/libs');
Loader::registerComposer();
