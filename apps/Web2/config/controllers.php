<?php

/*
 * Default controller if it is not specified
 * For example in the index
 */
$config['default'] = 'Main';


/*
 * Controller to execute for each excepcion code
 * For example the exception 404 executes the controller Exception:notFound
 * 0 for all non specified exception controllers
 */
$config['exceptions'] = array(
	0 => 'Exception:others',
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