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

//Logs folder
if (!is_dir(__DIR__.'/data/logs')) {
    mkdir(__DIR__.'/data/logs', 0777, true);
}

//Env file
if (!is_file(__DIR__.'/.env')) {
    copy(__DIR__.'/.env.example', __DIR__.'/.env');
}

//Init composer, save global variables
$composer = include __DIR__.'/vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

Fol::setGlobal('composer', $composer);
Fol::setGlobal('dotenv', $dotenv);

unset($composer, $dotenv);
