<?php
if (
    (php_sapi_name() === 'cli-server') &&
    ($_SERVER['SCRIPT_NAME'] !== '/index.php') &&
    (strpos($_SERVER['SCRIPT_NAME'], '/.') === false) &&
    is_file($_SERVER['SCRIPT_FILENAME'])
) {
    return false;
}

require dirname(__DIR__).'/bootstrap.php';

//Execute the app
App\App::runHttp();
