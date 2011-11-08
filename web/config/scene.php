<?php
$config['modules'] = array(
	'detection' => 'subfolder',
	'subfolder' => 'admin',
	'availables' => array('content', 'svn', 'database', 'gettext')
);

$config['exit_modes'] = array(
	'detection' => 'subfolder',
	'availables' => array('normal')
);

$config['languages'] = array(
	'detection' => 'subfolder',
	'default' => '',
	'availables' => array(
		'gl' => true,
		'es' => false
	)
);
?>