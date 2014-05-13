<?php

//php -S localhost:8000 index.php
if (php_sapi_name() === 'cli-server') {
	$file = __DIR__.$_SERVER['REQUEST_URI'];

	if (strpos($file, '?')) {
		$file = strstr($file, '?', true);
	}

	if (is_file($file)) {
		return false;
	}
}

require '../bootstrap.php';

App\App::run();
