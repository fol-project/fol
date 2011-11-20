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
		$cache_file = str_replace('/', '', $file);

		if ($this->Cache->file->exists($cache_file)) {
			$this->Output->setContent($this->Cache->file->get($cache_file));
			return;
		}

		$Stylecow = new \Stylecow\Stylecow;

		$css = $Stylecow->load(SCENE_PATH.$file)->transform(array(
			'Vendor_prefixes',
			'Variables',
			'Ie_filters',
			'Grid',
			'Matches',
			'Nested_rules',
		))->toString();

		$this->Output->setContent($css);

		$this->Cache->file->set($cache_file, $css);
	}

	public function js ($text) {
		echo "<p>500: $text</p>";
	}
}
?>