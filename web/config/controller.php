<?php
$config['default'] = 'Main';

$config['routes'] = array(
	'/ruta/(fixo)/(variable)' => array(
		'controller' => 'Main:seccion',
		'defaults' => array(
			'variable' => 'variable-defecto'
		)
	)
);
?>