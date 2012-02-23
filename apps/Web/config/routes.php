<?php
return array(
	array(
		'pattern' => '/',
		'controller' => array('Main', 'index')
	),
	array(
		'pattern' => '/ver/(section [0-9]+)',
		'controller' => array('Main', 'show')
	),
	array(
		'pattern' => '/mola',
		'controller' => function () {
			echo 'quepasa';
		}
	)
);
?>