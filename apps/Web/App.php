<?php
namespace Apps\Web;

use Fol\Request;

class App extends \Fol\App {

	public function bootstrap () {
		//return $this->runCache();

		$Request = Request::createFromGlobals();

		$Response = $this->execute($Request);

		$Response->send();
	}


	private function runCache () {
		$Request = Request::createFromGlobals();

		$this->Cache->File->setFolder($this->path.'cache/');

		if (!($Response = $this->Cache->File->get($Request->getId()))) {
			$Response = $this->execute($Request);
		}

		$Response->send();
	}
}
?>