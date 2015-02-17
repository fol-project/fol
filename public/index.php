<?php
require dirname(__DIR__).'/bootstrap.php';

//Execute the app
(new App\App())->runHttp(Fol\Http\Request::createFromGlobals())->send();
