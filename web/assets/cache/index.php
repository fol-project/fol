<?php
use Fol\Loader;

include('../../../../bootstrap.php');

//Register the apps
Loader::registerNamespace('Apps\\Web', BASE_PATH.'/web');

//Handle the request using the files controller and send the response
(new Apps\Web\App)->handleRequest(null, 'files')->send();
