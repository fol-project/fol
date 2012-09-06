<?php
use Fol\Http\Request;

include('../../../bootstrap.php');

(new Apps\Web\App)->handleFile(Request::createFromGlobals())->send();