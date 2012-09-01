<?php
use Fol\App;
use Fol\Http\Request;

include('../../bootstrap.php');

(new Apps\File\App)->handle(Request::createFromGlobals())->send();