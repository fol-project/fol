<?php
use Fol\Errors;

require '../bootstrap.php';


//Register errors
Errors::register();
Errors::displayErrors();
Errors::setLogFile(BASE_PATH.'/logs/php.log');


//Execute the app
$app = new App\App();
$app()->send();
