<?php
namespace Controllers;

use Fol\Controller;

abstract class Base extends Controller {
	public function __construct () {

		//Execute actions
		if ($this->Input->exists('action')) {
			$this->Actions->execute($this->Input->postGet('action'), 'Generic');

			$this->Input->delete('action');
		}

		$this->Templates->set('base', 'html.php');
	}
}
?>