<?php
//Original code from phpCan Image class (http://idc.anavallasuiza.com/)

namespace Image;

abstract class Image {

	/**
	 * public function transform ([string $operations])
	 *
	 * Executes a list of operations
	 * Returns this
	 */
	public function transform ($operations = '') {
		if (!$operations) {
			return $this;
		}

		$array_operations = $this->getOperations($operations);

		foreach ($array_operations as $operation) {
			$function = $operation['function'];
			$params = $operation['params'];

			switch ($function) {
				case 'flip':
				case 'flop':
					$this->$function();
					break;

				case 'convert':
				case 'alpha':
					$this->$function($params[0]);
					break;

				case 'zoomCrop':
				case 'rotate':
					$this->$function($params[0], $params[1]);
					break;

				case 'merge':
				case 'resize':
					$this->$function($params[0], $params[1], $params[2]);
					break;

				case 'crop':
					$this->$function($params[0], $params[1], $params[2], $params[3]);
					break;
			}
		}

		return $this;
	}



	/**
	 * private function getOperations (array $operations)
	 *
	 * Splits string operations and convert it to array
	 * Returns array
	 */
	private function getOperations ($operations) {
		$return = array();
		$array = explode('|', $operations);

		foreach ($array as $each) {
			$params = explode(',', $each);

			while (empty($params[0]) && (count($params) > 0)) {
				array_shift($params);
			}

			$return[] = array(
				'function' => array_shift($params),
				'params' => $params
			);
		}

		return $return;
	}



	/**
	 * public function get ([string $image])
	 *
	 * Gets the image object
	 * Returns this
	 */
	public function get ($image = '') {
		if ($image) {
			if (!$this->load($image)) {
				return false;
			}
		}

		return $this->image;
	}



	/**
	 * public function set ([object $image])
	 *
	 * Sets the image object
	 * Returns this
	 */
	public function set ($image) {
		$this->image = $image;

		return $this;
	}



	/**
	 * public function show ([bool $header])
	 *
	 * Shows the image and die
	 */
	public function show ($header = true) {

		//Show header mime-type
		if ($header && ($type = $this->getMimeType())) {
			header('Content-Type: '.$type);
		}

		echo $this->toString();

		die();
	}



	/**
	 * protected function position (int/string $position, int $size, int $canvas)
	 *
	 * Calculates the x/y position of the image
	 * Returns integer
	 */
	protected function position ($position, $size, $canvas) {
		if (is_int($position)) {
			return $position;
		}

		switch ($position) {
			case 'top':
			case 'left':
				$position = 0;
				break;

			case 'middle':
			case 'center':
				$position = ($canvas/2) - ($size/2);
				break;

			case 'right':
			case 'bottom':
				$position = $canvas - $size;
				break;

			default:
				$position = 0;
		}

		return $position;
	}



	/**
	 * protected function enlarge ($width, $height, $image_width, $image_height)
	 *
	 * Calculate if the image must be enlarge or not
	 * Returns boolean
	 */
	protected function enlarge ($width, $height, $image_width, $image_height) {
		$w = $h = false;

		if ($width && $width > $image_width) {
			$w = true;
		}
		if ($height && $height > $image_height) {
			$h = true;
		}

		return ($w || $h) ? true : false;
	}
}
?>