<?php
namespace Controllers;

use Fol\Controller;

class File extends Controller {
	private function getFile () {
		return implode('/', $this->Router->path).'.'.$this->Input->format;
	}

	public function css () {
		$file = $this->getFile();

		$Stylecow = new \Stylecow\Stylecow;

		$Stylecow->load(SCENE_PATH.$file)->transform(array(
			'Vendor_prefixes',
			'Variables',
			'Ie_filters',
			'Grid',
			'Matches',
			'Nested_rules',
		))->toString();

		$this->Output->setContentType('css');
		$this->Output->setCache(60);
		$this->Output->setContent($Stylecow->toString());

		$this->Cache->setPage($this->Output, 3600*24);
	}

	public function img () {
		$file = $this->getFile();

		$Image = new \Image\Gd;

		$Image->load(SCENE_PATH.$file)->transform($this->Input->get('transform'));

		$this->Output->setContentType($Image->getMimeType());
		$this->Output->setCache(60);
		$this->Output->setContent($Image->toString());

		$this->Cache->setPage($this->Output, 3600*24);
	}
}
?>