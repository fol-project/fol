<?php
$config['default'] = 'Main';

$config['routing'] = array(
	'/ver/(section)?/(post)' => array(
		'controller' => 'Main:show',
		'defaults' => array(
			'section' => 'Ola'
		)
	)
);
?>