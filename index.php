<?php
use Fol\Loader;
use Fol\App;

define('FOL_VERSION', '0.1a');
define('BASE_PATH', __DIR__.'/');
define('BASE_HTTP', preg_replace('|/+|', '/', '/'.preg_replace('|^'.realpath(getenv('DOCUMENT_ROOT')).'|i', '', BASE_PATH)));

include(BASE_PATH.'libraries/functions.php');
include(BASE_PATH.'libraries/Fol/Loader.php');

$Loader = new Loader;
$Loader->registerNamespace('Apps', BASE_PATH.'apps/');

$App = App::create('Web');
$App->setEnvironment('default');

$Response = $App->execute();

$Response->send();
?>