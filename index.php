<?php
use Fol\Errors;
use Fol\Loader;
use Fol\App;

define('FOL_VERSION', '0.1a');
define('BASE_PATH', __DIR__.'/');
define('BASE_HTTP', preg_replace('|/+|', '/', '/'.preg_replace('|^'.realpath(getenv('DOCUMENT_ROOT')).'|i', '', BASE_PATH)));

include(BASE_PATH.'libraries/functions.php');
include(BASE_PATH.'libraries/Fol/Loader.php');

Loader::register();
Loader::registerNamespace('Apps', BASE_PATH.'apps/');

Errors::register();

App::create('Web')->bootstrap();