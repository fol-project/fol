<?php
use Fol\App;
use Fol\Http\Request;

include('bootstrap.php');

(new Apps\Web\App)->handle(Request::createFromGlobals())->send();