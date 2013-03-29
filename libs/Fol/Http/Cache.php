<?php
/**
 * Fol\Http\Cache
 * 
 * Class to cache the request/response
 */
namespace Fol\Http;

class Cache {
	protected $Container;


	/**
	 * Constructor. Set a cache container to store the cached data
	 * 
	 * @param Fol\Http\ContainerInterface $Container The container instance
	 */
	public function __construct (ContainerInterface $Container = null) {
		$this->Container = $Container;
	}


	public function save (Request $Request) {
		if (static::isCacheable($Request)) {
			$id = $Request->getId();

			$this->Container->set($id, $Request->Response);
		}
	}



	/**
	 * Check if a request is cached and returns the response
	 * 
	 * @param Fol\Http\Request $Request The request instance.
	 * 
	 * @return boolean Fol\Http\Response The cached response or false
	 */
	public function getCachedResponse (Request $Request) {
		$id = $Request->getId();

		if (!($Response = $this->Container->get($id))) {
			return false;
		}

		if ($Response->Headers->has('Expires') && ($Response->Headers->getDateTime('Expires')->getTimestamp() < time())) {
			return false;
		}

		if ($Response->getAge() > $Response->getMaxAge()) {
			return false;
		}

		return $Response;
	}



	/**
	 * Check if a request can be cached
	 * 
	 * @param Fol\Http\Request $Request The request instance.
	 * 
	 * @return boolean True if it can be cached, false if not
	 */
	protected static function isCacheable (Request $Request) {
		if ($Request->getMethod() !== 'GET') {
			return false;
		}

		if ($Request->Response->getStatus() !== 200) {
			return false;
		}

		if ($Request->isAjax()) {
			return false;
		}

		return true;
	}
}
