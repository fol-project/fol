<?php
namespace Fol;

class Actions {

	/**
	 * public function __get ($name)
	 *
	 * Returns mixed
	 */
	public function __get ($name) {
		$class = 'Actions\\'.$name;

		if (class_exists($class)) {
			return $this->$name = new $class;
		}
	}



	/**
	 * public function execute (array $actions, [string $default_class])
	 *
	 * Execute actions automatically
	 * Returns boolean
	 */
	public function execute ($actions, $default_class = '') {
		global $Input;

		foreach ((array)$actions as $action) {
			list($class, $method) = explodeTrim(':', $action, 2);

			if (!$method && $default_class) {
				$method = $class;
				$class = $default_class;
			} else {
				return false;
			}

			$class = camelCase($class, true);
			$method = camelCase($method);

			if (!property_exists($this, $class)) {
				$class_name = 'Actions\\'.$class;

				if (class_exists($class_name) && method_exists($class_name, $method)) {
					$Method = new \ReflectionMethod($class_name, $method);
				} else {
					return false;
				}
			} else if (method_exists($this->$class, $method)) {
				$Method = new \ReflectionMethod($this->$class, $method);
			} else {
				return false;
			}

			$parameters = array();

			foreach ($Method->getParameters() as $Parameter) {
				$name = $Parameter->getName();

				if ($Input->exists($name)) {
					$parameters[] = $Input->get($name);
				} else if ($Parameter->isOptional()) {
					$parameters[] = $Parameter->getDefaultValue();
				} else {
					return false;
				}
			}

			call_user_func_array(array($this->$class, $method), $parameters);
		}
	}
}
?>