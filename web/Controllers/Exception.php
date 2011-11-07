<?php
namespace Controllers;

class Exception {
	public function notFound ($text) {
		echo "<p>404: $text</p>";
	}

	public function serverError ($text) {
		echo "<p>500: $text</p>";
	}
}
?>