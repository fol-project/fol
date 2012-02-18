<?php
namespace Fol;

interface Cache_Interface {

	public function set ($name, $value, $expire = 3600);
	public function get ($name);
	public function exists ($name);
	public function delete ($name);
}
?>