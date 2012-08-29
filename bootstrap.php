<?php
use Fol\Loader;
use Fol\Errors;

define('FOL_VERSION', '0.0.1-beta');
define('BASE_PATH', str_replace('\\', '/', __DIR__).'/');
define('BASE_URL', preg_replace('|/+|', '/', '/'.strtolower(preg_replace('|^'.str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])).'|i', '', BASE_PATH))));

include(BASE_PATH.'libs/Fol/Loader.php');

Loader::register();
Loader::setLibrariesPath(BASE_PATH.'libs');
Loader::registerNamespace('Apps', BASE_PATH.'apps');
Loader::registerComposer();

Errors::register();