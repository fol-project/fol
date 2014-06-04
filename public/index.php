<?php
if ((php_sapi_name() === 'cli-server') && ($_SERVER['SCRIPT_NAME'] !== '/index.php') && (strpos($_SERVER['SCRIPT_NAME'], '/.') === false) && is_file($_SERVER['SCRIPT_FILENAME'])) {
	return false;
}

require '../bootstrap.php';

App\App::run();
