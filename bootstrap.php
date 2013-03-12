<?php
use Fol\Loader;
use Fol\Errors;

define('FOL_VERSION', '0.1.0');
define('BASE_PATH', str_replace('\\', '/', __DIR__));
define('BASE_URL', preg_replace('|/+|', '/', strtolower(preg_replace('|^'.str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])).'|i', '', BASE_PATH))));
define('BASE_ABSOLUTE_URL', ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST']);

include(BASE_PATH.'/libs/Fol/Loader.php');

Loader::register();
Loader::setLibrariesPath(BASE_PATH.'/libs');
Loader::registerComposer();
Loader::registerNamespace('Apps', BASE_PATH.'/apps');

Errors::register(E_ALL);
Errors::displayErrors(true);