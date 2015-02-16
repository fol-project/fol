<?php
require dirname(__DIR__).'/bootstrap.php';

use App\App;
use Fol\Http\Request;

//Execute the app
(new App())->runHttp(Request::createFromGlobals())->send();
