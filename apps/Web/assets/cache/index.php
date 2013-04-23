<?php
include('../../../../bootstrap.php');

(new Apps\Web\App)->handle(Fol\Http\Request::createFromGlobals(), 'files')->send();