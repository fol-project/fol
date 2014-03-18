<?php
use Fol\Errors;

//php -S localhost:8000 index.php
if ((php_sapi_name() === 'cli-server') && is_file(__DIR__.$_SERVER['REQUEST_URI'])) {
	return false;
}

require '../bootstrap.php';

//Register errors
Errors::register();
Errors::displayErrors();
Errors::setPhpLogFile(BASE_PATH.'/logs/php.log');

//Execute the app
$app = new App\App();
$app()->send();
