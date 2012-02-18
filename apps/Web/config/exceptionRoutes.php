<?php
return array(
	array(
		'name' => 'HttpException',
		'controller' => array('Exception', 'notFound'),
		'code' => 404
	),
	array(
		'name' => 'HttpException',
		'controller' => array('Exception', 'serverError'),
		'code' => 505
	),
	array(
		'name' => 'HttpException',
		'controller' => array('Exception', 'error'),
	)
);
?>