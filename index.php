<?php
use Fol\Loader;
use Fol\Errors;

include('bootstrap.php');

//Register the apps here
Loader::registerNamespace('Apps\\Web', BASE_PATH.'/web');

//Show errors
Errors::displayErrors();

//Handle the request and send the response
$app = new Apps\Web\App;
$app()->send();
