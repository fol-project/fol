<?php
namespace Models;

use Fol\Model;

class Blog extends Model {
	public function select () {
		echo 'seleccionando un blog';
	}

	public function saySomething ($text) {
		echo '<p><strong>'.$text.'</strong></p>';
	}
}
?>