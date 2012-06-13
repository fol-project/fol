<?php
namespace Fol;

abstract class App {
	private $namespace;
	private $name;

	private $path;
	private $url;

	private $publicPath;
	private $publicUrl;

	public $Parent;



	/**
	 * static function create (string $name, [Object $Parent])
	 *
	 * Returns object
	 */
	static function create ($name, App $Parent = null, $url = null, $public = 'public') {
		$name = str_replace(' ', '', ucwords(strtolower(str_replace('-', ' ', $name))));

		$app = 'Apps\\'.$name.'\\App';

		if (class_exists($app)) {
			return new $app($Parent, $url, $public);
		}

		throw new InvalidArgumentException('"'.$app.'" is an invalid app class');
	}



	/**
	 * public function __construct ([Object $Parent])
	 *
	 * Returns none
	 */
	public function __construct (App $Parent = null, $url = null, $public = 'public') {
		$Class = new \ReflectionClass($this);

		$this->namespace = $Class->getNameSpaceName();
		$this->name = substr(strrchr($this->namespace, '\\'), 1);
		$this->path = str_replace('\\', '/', dirname($Class->getFileName())).'/';

		if (isset($Parent)) {
			$this->Parent = $Parent;
			$this->url = $Parent->getUrl($url);
		} else {
			$this->url = BASE_URL.$url;
		}

		if (substr($this->url, -1) !== '/') {
			$this->url .= '/';
		}

		$this->publicPath = BASE_PATH.$public.'/';
		$this->publicUrl = BASE_URL.$public.'/';
	}


	public function getName () {
		return $this->name;
	}


	public function getNameSpace () {
		return $this->namespace;
	}


	public function getPath ($path = null) {
		if (!empty($path) && $path[0] === '/') {
			$path = substr($path, 1);
		}

		return $this->path.$path;
	}


	public function getUrl ($path = null, array $get = null) {
		if (!empty($path) && $path[0] === '/') {
			$path = substr($path, 1);
		}

		if (isset($get)) {
			return $this->url.$path.((strpos($path, '?') === false) ? '?' : '&').http_build_query($get);
		}

		return $this->url.$path;
	}


	public function getPublicPath ($path = null) {
		if (!empty($path) && $path[0] === '/') {
			$path = substr($path, 1);
		}

		return $this->publicPath.$path;
	}


	public function getPublicUrl ($path = null) {
		if (!empty($path) && $path[0] === '/') {
			$path = substr($path, 1);
		}

		return $this->publicUrl.$path;
	}
}
?>
