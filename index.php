<?php
define('FOL_VERSION', '0.1a');

define('BASE_PATH', __DIR__.'/');
define('BASE_HTTP', preg_replace('|/+|', '/', '/'.preg_replace('|^'.realpath(getenv('DOCUMENT_ROOT')).'|i', '', BASE_PATH)));
define('ENVIRONMENT', 'default');

include(BASE_PATH.'library/functions.php');

use Fol\Config;
use Fol\Router;

$Config = new Config();

//Scenes config
$Config->set('scenes', array(
	'web' => array(
		'path' => BASE_PATH.'web/',
		'detection' => 'subfolder'
	)
));

$Router = new Router();

include(SCENE_PATH.'bootstrap.php');
?>