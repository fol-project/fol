<?php
namespace Controllers;

use Fol\Templates;

abstract class Base {
	protected $Templates;

	public function __construct () {
		$this->Templates = new Templates;

		$this->Templates->set('base', 'html.php');
	}
}
?>