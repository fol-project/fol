<?php
return array(

	/*
	 * Default controller if it is not specified
	 * For example in the index
	 */
	'default' => 'Main',


	/*
	 * Mode the controller will be selected
	 * 1: Only 
	 */
	'allow_undefined_routings' => true,


	/*
	 * Controllers to execute if HttpException or ErrorException are throw
	 */
	'exceptions' => array(
		'HttpException' => 'Exception:http',
		'ErrorException' => 'Exception:error'
	),


	/*
	 * Defined routings with controller
	 */
	'routing' => array(
		'ver_texto' => array(
			'pattern' => 'ver/(section [0-9]+)',
			'controller' => 'Main:show',
			'parameters' => array('section' => 34),
			'method' => 'GET',
			'scheme' => 'http'
		),
	)
);
?>