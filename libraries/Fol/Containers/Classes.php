<?php
namespace Fol\Containers;

class Classes extends Container {

	/**
	 * public function get ([string $name])
	 *
	 * Gets one or all parameters
	 * Returns array
	 */
	public function get ($name = null) {
		if (func_num_args() === 0) {
			return $this->items;
		}

		if (($data = $this->items[$name]) && class_exists($data['class'])) {
			return $data;
		}
	}



	/**
	 * public function set (string $name, string $class, array $arguments)
	 *
	 * Sets classes with an unique name
	 * Returns none
	 */
	public function set ($name, $class = null, array $arguments = array()) {
		if (is_array($name)) {
			foreach ($name as $class) {
				$this->set($class[0], $class[1], $class[2]);
			}

			return;
		}

		$this->items[$name] = array(
			'class' => $class,
			'arguments' => $arguments
		);
	}



	/**
	 * public function getInstance (string $name, [array $arguments])
	 *
	 * Create a new instance of a class
	 * Returns object/false
	 */
	public function getInstance ($name, $arguments = null) {
		if ($data = $this->get($name)) {
			$class = $data['class'];

			$parameters = is_array($arguments) ? $arguments : $data['arguments'];

			if ($parameters) {
				$Class = new \ReflectionClass($class);

				return $Class->newInstanceArgs($parameters);
			}

			return new $class;
		}

		return false;
	}
}
?>