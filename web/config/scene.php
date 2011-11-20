<?php
$config['languages'] = array(
	'detection' => 'subfolder',
	'default' => '',
	'availables' => array(
		'gl' => true,
		'es' => false
	)
);

$config['autoload'] = array(
	'Actions' => 'Fol\\Actions',
	'Cache' => 'Fol\\Cache',
	'Models' => 'Fol\\Models',
	'Session' => 'Fol\\Session',
	'Templates' => 'Fol\\Templates',
);
?>