<?php
use Fol\Loader;
use Fol\Errors;

include('bootstrap.php');

//Register the apps here

//Show errors
Errors::displayErrors();

//Handle the request and send the response
(new Apps\Web\App)->handleRequest()->send();
