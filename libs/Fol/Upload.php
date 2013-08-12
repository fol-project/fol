<?php
/**
 * Fol\Upload
 * 
 * Simple class to manage uploaded files from various sources (POST, urls, base64, etc)
 */
namespace Fol;

class Upload {
	const TYPE_UPLOAD = 1;
	const TYPE_URL = 2;
	const TYPE_BASE64 = 3;

	private $input;
	private $output;
	private $base_dir;

	public static function isSavable ($input) {
		if (empty($input)) {
			return false;
		}

		return static::getType($input) ? true : false;
	}

	public static function getType ($input) {
		if (is_array($input)) {
			return static::TYPE_UPLOAD;
		}

		if (is_string($input)) {
			if (strpos($input, '://')) {
				return static::TYPE_URL;
			}

			if (strpos($input, 'data:') === 0) {
				return static::TYPE_BASE64;
			}
		}

		return false;
	}


	public function __construct ($base_dir = null) {
		if ($base_dir !== null) {
			$this->setBaseDir($base_dir);
		}
	}

	public function setBaseDir ($base_dir) {
		$this->base_dir = $base_dir;
	}

	public function setInputOutput ($input, $output = null) {
		$this->type = static::getType($input);

		switch ($this->type) {
			case static::TYPE_UPLOAD:
				$this->setInputOutputFromUploads($input, $output);
				break;

			case static::TYPE_URL:
				$this->setInputOutputFromUrl($input, $output);
				break;

			case static::TYPE_BASE64:
				$this->setInputOutputFromBase64($input, $output);
				break;
		}
	}

	public function getOutput () {
		return $this->output;
	}

	public function getDestination () {
		return $this->base_dir.$this->output;
	}


	/**
	 * Save a file
	 * 
	 * @param string $filename An optional new filename
	 * 
	 * @return string The filename of the saved file or false.
	 */
	public function save () {
		$destination = $this->getDestination();

		if (!is_writable(dirname($destination))) {
			throw new \Exception("Permissions error for $destination");
		}

		switch ($this->type) {
			case static::TYPE_UPLOAD:
				return $this->saveFromUploads($destination);

			case static::TYPE_URL:
				return $this->saveFromUrl($destination);

			case static::TYPE_BASE64:
				return $this->saveFromBase64($destination);
		}

		throw new \Exception("Upload type not valid");
	}


	/**
	 * Save a file from an URL
	 *
	 * @param string $filename An optional new filename
	 * 
	 * @return boolean
	 */
	private function saveFromUrl ($destination) {
		$content = @file_get_contents($this->input);

		if (empty($content) || !@file_put_contents($destination, $content)) {
			return false;
		}

		return true;
	}

	private function setInputOutputFromUrl ($input, $output) {
		if ($output === null) {
			$output = pathinfo($input, PATHINFO_BASENAME);
		} else if (!pathinfo($output, PATHINFO_EXTENSION) && ($extension = pathinfo(parse_url($input, PHP_URL_PATH), PATHINFO_EXTENSION))) {
			$output .= ".$extension";
		}

		$this->input = $input;
		$this->output = $output;
	}


	/**
	 * Save a file from a base64 string
	 *
	 * @param string $filename An optional new filename
	 * 
	 * @return boolean
	 */
	private function saveFromBase64 ($destination) {
		if (!@file_put_contents($destination, base64_decode($this->input))) {
			return false;
		}

		return true;
	}

	private function setInputOutputFromBase64 ($input, $output) {
		if (empty($input) || (strpos($input, 'data:') !== 0)) {
			return false;
		}

		$fileData = explode(';base64,', $input, 2);

		if (!pathinfo($output, PATHINFO_EXTENSION) && preg_match('|data:\w+/(\w+)|', $fileData[0], $match)) {
			$output .= '.'.$match[1];
		}

		$this->input = $fileData[1];
		$this->output = $output;

		return true;
	}


	/**
	 * Save an uploaded file
	 *
	 * @param string $filename An optional new filename
	 * 
	 * @return boolean
	 */
	private function saveFromUploads ($destination) {
		if (!@rename($this->input, $destination)) {
			return false;
		}

		return true;
	}

	private function setInputOutputFromUploads ($input, $output) {
		if (empty($input['tmp_name']) || !empty($input['error'])) {
			return false;
		}

		if ($output === null) {
			$output = $input['name'];
		}

		if (!pathinfo($output, PATHINFO_EXTENSION) && ($output = pathinfo($input['name'], PATHINFO_EXTENSION))) {
			$output .= ".$extension";
		}

		$this->input = $input['tmp_name'];
		$this->output = $output;

		return true;
	}
}
