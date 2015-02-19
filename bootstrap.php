<?php
if (!ini_get('date.timezone')) {
	ini_set('date.timezone', 'Europe/Madrid');
}

require __DIR__.'/vendor/autoload.php';

Fol\Fol::init(__DIR__, 'env.php');
