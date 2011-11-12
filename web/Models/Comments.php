<?php
namespace Models;

use Fol\Model;

class Comments extends Model {
	public function sayHello () {
		print_r($this->Controller->Models->Blog->select());
	}

	public function saySomething ($text) {
		echo '<p><strong>'.$text.'</strong></p>';
	}
}
?>