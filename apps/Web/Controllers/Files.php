<?php
namespace Apps\Web\Controllers;

use Fol\Http\Response;

class Files {
	private $cache = true;

	public function __construct ($App, $Request) {
		$this->App = $App;
		$this->Request = $Request;
	}

	/** Example of preprocessed CSS file

	public function css ($file) {
		$filepath = $this->App->assetsPath.$file;
	
		//Preprocess the file and get the content
		$result = myCssPreprocessor($filepath);

		//Save the result in the cache
		if ($this->cache === true) {
			file_put_contents($this->App->getCacheFilePath($file), $result);
		}

		//Returns the result
		$Response = new Response($result);
		$Response->setContentType('css');

		return $Response;
	}
}
?>