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
}
?>