<?php
error_reporting(E_ALL & ~E_NOTICE);

define('FOL_VERSION', '0.1a');

putenv('DOCUMENT_ROOT='.realpath(getenv('DOCUMENT_ROOT')));
define('BASE_PATH', __DIR__.'/');
define('BASE_WWW', preg_replace('#/+#', '/', '/'.preg_replace('|^'.getenv('DOCUMENT_ROOT').'|i', '', BASE_PATH)));
define('ENVIRONMENT', 'default');

require_once(BASE_PATH.'Fol/functions.php');

$Config = new Fol\Config();
$Router = new Fol\Router();
$Input = new Fol\Input();

$Router->go();
?>