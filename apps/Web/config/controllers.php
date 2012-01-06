<?php

/*
 * Default controller if it is not specified
 * For example in the index
 */
$config['default'] = 'Main';


/*
 * Controllers to execute if HttpException or ErrorException are throw
 */
$config['http_exception'] = 'Exception:http';
$config['error_exception'] = 'Exception:error';


/*
 * Routing configuration
 */
$config['routing'] = array(
	//'/ver/(section [0-9]+)/(post)' => 'Main:show',
);
?>