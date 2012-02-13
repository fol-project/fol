<?php
namespace Fol;

abstract class Controller {
	public $App;
	public $Request;
	public $Models;
	public $Views;


	/**
	 * public function __construct ($App, Fol\Http\Request $Request)
	 *
	 * Returns none
	 */
	public function __construct ($App, Http\Request $Request) {
		$this->App = $App;
		$this->Request = $Request;
		$this->Models = $this->App->Services->get('Models', array($this));
		$this->Views = $this->App->Services->get('Views', array($this));
	}
}
?>