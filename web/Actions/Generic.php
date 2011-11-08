<?php
namespace Actions;

class Generic {
	public function __construct () {
	}

	public function sayHello () {
		$this->saySomething('Hello!');
	}

	public function saySomething ($text) {
		echo '<p><strong>'.$text.'</strong></p>';
	}
}
?>