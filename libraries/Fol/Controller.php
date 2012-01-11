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
		$this->Models = $this->App->Services->get('Models', array($this));
		$this->Views = $this->App->Services->get('Views', array($this));
	}
}
?>