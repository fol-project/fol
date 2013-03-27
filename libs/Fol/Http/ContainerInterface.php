<?php
/**
 * Fol\Http\ContainerInterface
 * 
 * Simple interface used to store variables in different ways
 */
namespace Fol\Http;

interface ContainerInterface {


	/**
	 * Gets one or all parameters.
	 * 
	 * $params->get() Returns all parameters
	 * $params->get('name') Returns just this parameter
	 * 
	 * @param string $name The parameter name
	 * @param mixed $default The default value if the parameter is not set
	 * 
	 * @return mixed The parameter value or the default
	 */
	public function get ($name = null, $default = null);


	/**
	 * Sets one parameter or various new parameters
	 * 
	 * @param string $name The parameter name. You can define an array with name => value to insert various parameters
	 * @param mixed $value The parameter value.
	 */
	public function set ($name, $value = null);


	/**
	 * Deletes one or all parameters
	 * 
	 * $params->delete('name') Deletes one parameter
	 * $params->delete() Deletes all parameter
	 * 
	 * @param string $name The parameter name
	 */
	public function delete ($name = null);


	/**
	 * Checks if a parameter exists
	 * 
	 * @param string $name The parameter name
	 * 
	 * @return boolean True if the parameter exists (even if it's null) or false if not
	 */
	public function has ($name);
}
?>