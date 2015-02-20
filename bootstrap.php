<?php
if (!ini_get('date.timezone')) {
	ini_set('date.timezone', 'Europe/Madrid');
}

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require __DIR__.'/vendor/autoload.php';

Fol\Fol::init(__DIR__, 'env.php');
