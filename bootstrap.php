<?php
define('ACCESS_INTERFACE', (php_sapi_name() === 'cli') ? 'cli' : 'http');
define('BASE_PATH', str_replace('\\', '/', __DIR__));


//Environment variables
foreach (require_once 'environment.php' as $name => $value) {
	define($name, $value);
}

unset($name, $value);


//Init the composer loader
$composer = require BASE_PATH.'/vendor/autoload.php';
