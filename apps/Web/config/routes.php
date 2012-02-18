<?php
return array(
	'index' => array(
		'pattern' => '/',
		'controller' => array('Main', 'index')
	),
	'ver-texto' => array(
		'pattern' => '/ver/(section [0-9]+)',
		'controller' => array('Main', 'show')
	),
	'test' => array(
		'pattern' => '/mola',
		'controller' => function () {
			echo 'quepasa';
		}
	)
);
?>