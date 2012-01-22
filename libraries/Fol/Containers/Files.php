<?php
namespace Fol\Containers;

class Files extends Input {

	/**
	 * public function __construct (array $parameters)
	 *
	 * Detects request info
	 * Returns none
	 */
	public function __construct (array $parameters = array()) {
		if ($parameters) {
			$parameters = $this->fixArray($parameters);
		}

		$this->items = $parameters;
	}


	
	/**
	 * private function fixArray (array $files)
	 *
	 * Fix the $files order by converting from default wierd schema
	 * [first][name][second][0], [first][error][second][0]...
	 * to a more straightforward one.
	 * [first][second][0][name], [first][second][0][error]...
	 * Returns array
	 */
	private function fixArray ($files) {
		if (isset($files['name'], $files['tmp_name'], $files['size'], $files['type'], $files['error'])) {
			return $this->moveToRight($files);
		}

		foreach ($files as &$file) {
			$file = $this->fixArray($file);
		}

		return $files;
	}



	/**
	 * private function moveToRight (array $files)
	 *
	 * Private function used by fixArray
	 * Returns array
	 */
	private function moveToRight ($files) {
		$results = array();

		foreach($files['name'] as $index => $name) {
			$reordered = array(
				'name' => $files['name'][$index],
				'tmp_name' => $files['tmp_name'][$index],
				'size' => $files['size'][$index],
				'type' => $files['type'][$index],
				'error' => $files['error'][$index]
			);

			if (is_array($name)) {
				$reordered = $this->moveToRight($reordered);
			}

			$results[$index] = $reordered;
		}

		return $results;
	}
}
?>