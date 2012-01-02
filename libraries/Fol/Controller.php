<?php
namespace Fol;

class Controller {
	protected $App;
	protected $Request;


	/**
	 * public function __construct ($App, Fol\Request $Request)
	 *
	 * Returns none
	 */
	public function __construct ($App, Request $Request) {
		$this->App = $App;
		$this->Request = $Request;
	}
}
?>