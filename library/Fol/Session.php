<?php
namespace Fol;

class Session {
	public $message = array();



	/**
	 * public function __construct (void)
	 *
	 * Detects request info
	 * Returns none
	 */
	public function __construct () {
		$this->detectMessage();
	}



	/**
	 * private function detectMessage (void)
	 *
	 * Loads the flash message
	 * Returns string/false
	 */
	private function detectMessage () {
		$message_text = $this->scene.'-'.$this->module.'-message_text';
		$message_type = $this->scene.'-'.$this->module.'-message_type';

		$this->message['inbox'] = $this->get($message_text);
		$this->message['type'] = $this->get($message_type);
		$this->message['outbox'] = '';

		$this->delete($message_text);
		$this->delete($message_type);
	}



	/**
	 * public function setMessage (string $text, [string $type])
	 *
	 * Create or edit the flash message
	 * Returns none
	 */
	public function setMessage ($text, $type = null) {
		$this->message['input'] = $this->message['input'] = $text;
		$this->message['type'] = $type;
	}



	/**
	 * public function getMessage (void)
	 *
	 * Returns string
	 */
	public function getMessage () {
		return $this->message['input'];
	}



	/**
	 * public function getMessageType (void)
	 *
	 * Returns string
	 */
	public function getMessageType () {
		return $this->message['type'];
	}



	/**
	 * public function get (string $name, [string $filter])
	 *
	 * Get (and optionally filter) a cookie value
	 * Returns mixed
	 */
	public function get ($name, $filter = null) {
		if ($filter) {
			$filter = $this->getSanitizeFilter($filter);

			return filter_input(INPUT_COOKIE, $name, $filter[0], $filter[1]);
		}

		return filter_input(INPUT_COOKIE, $name);
	}



	/**
	 * public function set (string $name, string $value, [int $duration])
	 *
	 * Returns boolean
	 */
	public function set ($name, $value, $duration = null) {
		if (is_null($duration)) {
			$duration = 86400; //one day
		}

		return setcookie($name, $value, time() + $duration, BASE_WWW);
	}



	/**
	 * public function delete (string $name)
	 *
	 * Returns boolean
	 */
	public function delete ($name) {
		return setcookie($name, '', 1, BASE_WWW);
	}



	/**
	 * private function getSanitizeFilter ($name)
	 *
	 * Returns array
	 */
	private function getSanitizeFilter ($name) {
		switch ($name) {
			case 'string':
			return array(FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);

			case 'int':
			return array(FILTER_SANITIZE_NUMBER_INT);

			case 'bool':
			return array(FILTER_VALIDATE_BOOLEAN);

			case 'float':
			return array(FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

			case 'special_chars':
			return array(FILTER_SANITIZE_SPECIAL_CHARS);

			case 'encoded':
			return array(FILTER_SANITIZE_ENCODED);

			case 'url':
			return array(FILTER_SANITIZE_URL);

			case 'email':
			return array(FILTER_SANITIZE_EMAIL);
		}

		return array(FILTER_UNSAFE_RAW);
	}
}
?>