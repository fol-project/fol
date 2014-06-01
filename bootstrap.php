<?php
use Fol\Http\Globals;

define('ACCESS_INTERFACE', (php_sapi_name() === 'cli') ? 'cli' : 'http');
define('BASE_PATH', str_replace('\\', '/', __DIR__));


//Init the composer loader
$composer = require BASE_PATH.'/vendor/autoload.php';


//Define basic constants
$constants = require 'constants.php';

if (php_sapi_name() === 'cli-server') {
	$constants['BASE_URL'] = Globals::getScheme().'://'.Globals::get('SERVER_NAME').':'.Globals::getPort();
	$constants['PUBLIC_DIR'] = '';
}

foreach ($constants as $name => $value) {
	define($name, $value);
}

unset($name, $value, $constants);
