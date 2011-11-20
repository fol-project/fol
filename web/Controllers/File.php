<?php
namespace Controllers;

use Fol\Controller;

class File extends Controller {
	private function getFile () {
		return implode('/', $this->Router->path).'.'.$this->Input->format;
	}

	public function css () {
		$this->Output->setContentType('css');
		$this->Output->setCache(60);

		$file = $this->getFile();
		$cache_file = str_replace('/', '-', $file);

		if ($this->Cache->File->exists($cache_file)) {
			return $this->Output->setContent($this->Cache->File->get($cache_file));
		}

		$Stylecow = new \Stylecow\Stylecow;

		$Stylecow->load(SCENE_PATH.$file)->transform(array(
			'Vendor_prefixes',
			'Variables',
			'Ie_filters',
			'Grid',
			'Matches',
			'Nested_rules',
		));

		$css = $Stylecow->toString();

		$this->Cache->File->set($cache_file, $css);

		$this->Output->setContent($css);
	}

	public function js ($text) {
		echo "<p>500: $text</p>";
	}
}
?>