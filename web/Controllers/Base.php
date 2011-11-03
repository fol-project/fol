<?php
namespace Controllers;

abstract class Base {
	public function __construct () {
		echo memory_get_usage().'<br>';
	}
}
?>