<?php
use Fol\Http\Globals;

define('BASE_PATH', str_replace('\\', '/', __DIR__));


//Init the composer loader
$composer = require BASE_PATH.'/vendor/autoload.php';


//Define basic constants
$constants = require 'constants.php';

if (php_sapi_name() === 'cli-server') {
	$constants['BASE_URL'] = Globals::getScheme().'://'.Globals::get('SERVER_NAME').':'.Globals::getPort();
	define('PUBLIC_DIR', '');
} else if (substr(Globals::get('PHP_SELF'), -17) === '/public/index.php') {
	define('PUBLIC_DIR', '/public');
} else {
	define('PUBLIC_DIR', '');
}

foreach ($constants as $name => $value) {
	define($name, $value);
}

unset($name, $value, $constants);
