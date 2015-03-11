<?php
if (!ini_get('date.timezone')) {
    ini_set('date.timezone', 'Europe/Madrid');
}

//Error configuration
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__.'/data/log/php');

require __DIR__.'/vendor/autoload.php';

Fol::init(__DIR__, 'env.php');
