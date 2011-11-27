<?php
namespace Fol;

class Cache {
	public $item;

	/**
	 * public function __get ($name)
	 *
	 * Returns object/none
	 */
	public function __get ($name) {
		$class = 'Fol\\Cache\\'.$name;

		if (class_exists($class)) {
			return $this->$name = new $class;
		}
	}



	/**
	 * public function setPage (Fol\Output $Output, [int $expire])
	 *
	 * Saves the Output of the current page
	 * Returns boolean
	 */
	public function setPage (Output $Output, $expire = 3600) {
		if ($page_id = $this->pageId()) {
			$this->File->set($page_id, $Output, $expire);

			return true;
		}

		return false;
	}



	/**
	 * public function getPage (&$Output)
	 *
	 * Returns the cached Output of the current page
	 * Returns boolean
	 */
	public function getPage (&$Output) {
		if ($page_id = $this->pageId()) {
			if ($this->File->exists($page_id)) {
				$Output = $this->File->get($page_id);

				return true;
			}
		}

		return false;
	}



	/**
	 * private function pageId ()
	 *
	 * Generates an unique id for the current page
	 * Returns string/false
	 */
	private function pageId () {
		if (getenv('REQUEST_METHOD') === 'GET') {
			return getenv('REQUEST_URI');
		}

		return false;
	}
}
?>