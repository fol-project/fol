<?php
namespace Fol;

class Model {
	protected $Controller;

	/**
	* public function __construct ($Controller)
	*
	* Returns mixed
	*/
	public function __construct ($Controller) {
		$this->Controller = $Controller;
	}
}
?>