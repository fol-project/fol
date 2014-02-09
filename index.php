<?php
use Fol\Errors;

require 'bootstrap.php';


//Register errors
Errors::register();
Errors::displayErrors();


//Execute the app
$app = new App\App();
$app()->send();
