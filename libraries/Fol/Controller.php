<?php
namespace Fol;

abstract class Controller {
	protected $App;
	protected $Request;
	protected $Models;
	protected $Views;


	/**
	 * public function __construct ($App, Fol\Request $Request)
	 *
	 * Returns none
	 */
	public function __construct ($App, Request $Request) {
		$this->App = $App;
		$this->Request = $Request;
		$this->Models = new Models($this->App);
		$this->Views = new Views($this->App);
	}
}
?>