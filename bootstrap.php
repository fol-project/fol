<?php
use Fol\Loader;
use Fol\Errors;

define('FOL_VERSION', '0.2.1');
define('BASE_PATH', str_replace('\\', '/', __DIR__));
define('BASE_URL', preg_replace('|/+|', '/', strtolower(preg_replace('|^'.str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])).'|i', '', BASE_PATH))));

if (php_sapi_name() === 'cli') {
	define('BASE_ABSOLUTE_URL', 'http://localhost');
} else {
	define('BASE_ABSOLUTE_URL', ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST']);
}

include(BASE_PATH.'/libs/Fol/Loader.php');

Loader::register();
Loader::setLibrariesPath(BASE_PATH.'/libs');
Loader::registerComposer();

//Register the apps
Loader::registerNamespace('Apps\\Web', BASE_PATH.'/web');

Errors::register();
Errors::displayErrors();
