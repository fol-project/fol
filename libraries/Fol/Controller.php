<?php
namespace Fol;

abstract class Controller {
	public $App;
	public $Request;
	public $Models;
	public $Views;


	/**
	 * public function __construct ($App, Fol\Request $Request)
	 *
	 * Returns none
	 */
	public function __construct ($App, Request $Request) {
		$this->App = $App;
		$this->Request = $Request;
		$this->Models = new Models($this);
		$this->Views = new Views($this);
	}
}
?>