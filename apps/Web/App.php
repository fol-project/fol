<?php
namespace Apps\Web;

use Fol\Request;

class App extends \Fol\App {

	public function bootstrap () {
		$this->Services->register('CacheFile', 'Fol\\Cache_File', array($this->path.'cache/'));

		//return $this->runCache();

		$Request = Request::createFromGlobals();

		$Response = $this->execute($Request);

		$Response->send();
	}


	private function runCache () {
		$Request = Request::createFromGlobals();
		$CacheFile = $this->Services->get('CacheFile');

		if (!($Response = $CacheFile->get($Request->getId()))) {
			$Response = $this->execute($Request);
		}

		$Response->send();
	}
}
?>