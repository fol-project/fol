<?php

//Timezone configuration
if (!ini_get('date.timezone')) {
    ini_set('date.timezone', 'Europe/Madrid');
}

//Error configuration and security
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__.'/data/logs/php');
ini_set('expose_php', 0);

//Init composer
include __DIR__.'/vendor/autoload.php';

//Init .env variables
(new Dotenv\Dotenv(__DIR__))->load();

Env::init();
