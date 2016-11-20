<?php

if (php_sapi_name() === 'cli-server') {
    $path = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

    if ($path !== '/' && (is_file(__DIR__.$path) || is_file(rtrim(__DIR__.$path, '/').'/index.html'))) {
        return false;
    }
}

require dirname(__DIR__).'/bootstrap.php';

//Execute the app
App\App::run();
