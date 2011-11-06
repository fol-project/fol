<?php
namespace Controllers;

use Fol\Controller;

abstract class Base extends Controller {
	public function __construct () {
		$this->Templates->set('base', 'html.php');
	}
}
?>