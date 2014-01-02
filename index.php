<?php
use Fol\Loader;
use Fol\Errors;

define('ENVIRONMENT', 'development');

include('bootstrap.php');

//Register errors
Errors::register();
Errors::displayErrors();

//Register the apps here


//Handle the request and send the response
