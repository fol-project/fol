<?php
return array(

	/*
	 * Default controller if it is not specified
	 * For example in the index
	 */
	'default' => 'Main',


	/*
	 * Controllers to execute if HttpException or ErrorException are throw
	 */
	'HttpException' => 'Exception:http',
	'ErrorException' => 'Exception:error',


	'routing' => array(
		'ver_texto' => array(
			'pattern' => 'ver/(section [0-9]+)/',
			'controller' => 'Main:show',
			'defaults' => array('section' => 34),
			'method' => 'GET',
			'scheme' => 'http'
		),
	)
);
?>