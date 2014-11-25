<?php
use Fol\Http\Globals;

define('BASE_PATH', str_replace('\\', '/', __DIR__));


//Init the composer loader
$composer = require BASE_PATH.'/vendor/autoload.php';


//Define basic constants
$constants = require 'constants.local.php';

if (php_sapi_name() === 'cli-server') {
	$constants['BASE_URL'] = Globals::getScheme().'://'.Globals::getHost().':'.Globals::getPort();
}

foreach ($constants as $name => $value) {
	define($name, $value);
}

unset($name, $value, $constants);
