<?php
namespace Apps\File\Controllers;

use Fol\Http\Response;

class Index {
	private $cache = true;

	public function __construct ($App, $Request) {
		$this->App = $App;
		$this->Request = $Request;
	}

	public function css ($file) {
		$Stylecow = \Stylecow\Parser::parseFile($this->App->assetsPath.$file);
		$Stylecow->applyPlugins(array(
			'VendorPrefixes',
			'Variables',
			'Color',
			'NestedRules'
		));

		if ($this->cache === true) {
			file_put_contents($this->cachePath($file), $Stylecow->toString());
		}

		$Response = new Response($Stylecow->toString());
		$Response->setContentType('css');

		return $Response;
	}

	public function jpg ($file) {
		return $this->image($file);
	}

	public function jpeg ($file) {
		return $this->image($file);
	}

	public function png ($file) {
		return $this->image($file);
	}

	private function image ($file) {
		$info = pathinfo($file);

		if (strpos($info['filename'], '__')) {
			list($operations, $filename) = explode('__', $info['filename'], 2);
		}

		$filepath = $this->App->assetsPath.$info['dirname'].'/'.$filename.'.'.$info['extension'];

		if (is_file($filepath)) {
			$Image = \Imagecow\Image::create();

			$Image->load($filepath)->transform($operations);

			if ($this->cache === true) {
				$file = $this->cachePath($file);
				$Image->save($file);
			}

			$Image->show();
		}
	}

	private function cachePath ($file) {
		$path = dirname($file);

		if (!is_dir($this->App->assetsPath.'cache/'.$path)) {
			mkdir($this->App->assetsPath.'cache/'.$path, 0777, true);
		}

		return $this->App->assetsPath.'cache/'.$file;
	}
}
?>