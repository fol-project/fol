<?php
use Fol\App;
use Fol\Http\Request;

include('bootstrap.php');

App::create('Web')->handle(Request::createFromGlobals())->send();