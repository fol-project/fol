<?php

//php -S localhost:8000 index.php
if ((php_sapi_name() === 'cli-server') && is_file(__DIR__.$_SERVER['REQUEST_URI'])) {
	return false;
}

require '../bootstrap.php';

App\App::run();
