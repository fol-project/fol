<?php
$config['default'] = 'Main';
$config['exceptions'] = array(
	404 => 'Exception:notFound',
	500 => 'Exception:serverError'
);

$config['routing'] = array(
	'/ver/(section)?/(post)' => array(
		'controller' => 'Main:show',
		'defaults' => array(
			'section' => 'Ola'
		)
	)
);
?>