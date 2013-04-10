<?php
include('bootstrap.php');

$app = 'Apps\\'.ucfirst($argv[1]).'\\Cli';

if (class_exists($app)) {
	$App = (new $app)->invoke($argv[2], array_slice($argv, 3));
}

echo "\n";
