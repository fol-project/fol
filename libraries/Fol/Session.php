<?php
namespace Fol;

class Session {
	private $message = array();



	/**
	 * public function __construct (void)
	 *
	 * Detects request info
	 * Returns none
	 */
	public function __construct () {
		$this->message['input'] = filter_input(INPUT_COOKIE, 'message_text');
		$this->message['type'] = filter_input(INPUT_COOKIE, 'message_type');
		$this->message['output'] = '';

		setcookie('message_text', '', 1, BASE_WWW);
		setcookie('message_type', '', 1, BASE_WWW);
	}



	/**
	 * public function setMessage (string $text, [string $type])
	 *
	 * Create or edit the flash message
	 * Returns none
	 */
	public function setMessage ($text, $type = null) {
		$this->message['input'] = $text;
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
	 * public function saveMessage (void)
	 *
	 * Returns boolean
	 */
	public function saveMessage () {
		if ($this->message['outbox']) {
			setcookie('message_text', $this->message['outbox'], 0, BASE_WWW);
		}
		if ($this->message['type']) {
			setcookie('message_type', $this->message['type'], 0, BASE_WWW);
		}
	}
}
?>