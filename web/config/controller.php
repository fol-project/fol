<?php

/*
 * Classes which will be autoloaded using $this->class_name
 * For example: $this->Actions
 */
$config['autoload'] = array(
	'Actions' => 'Fol\\Actions',
	'Cache' => 'Fol\\Cache',
	'Models' => 'Fol\\Models',
	'Session' => 'Fol\\Session',
	'Templates' => 'Fol\\Templates',
);


/*
 * Default controller if it is not specified
 * For example in "/"
 */
$config['default'] = 'Main';


/*
 * Controller to execute for each excepcion code
 * For example the exception 404 executes the controller Exception:notFound
 */
$config['exceptions'] = array(
	404 => 'Exception:notFound',
	500 => 'Exception:serverError'
);


/*
 * Routing configuration
 */
$config['routing'] = array(
	//'/ver/(section [0-9]+)/(post)' => 'Main:show',
);
?>