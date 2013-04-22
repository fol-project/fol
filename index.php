<?php
use Fol\Http\Request;

include('bootstrap.php');

if (php_sapi_name() === 'cli') {
	$Request = Request::createFromCli($argv);
} else {
	$Request = Request::createFromGlobals();
}

(new Apps\Web\App)->handle($Request)->send();
