<?php
namespace Fol\Http;

class Cookies {
	protected $items = array();


	/**
	 * public function __toString ()
	 *
	 * Converts all cookies to a string
	 */
	public function __toString () {
		$text = '';
		$time = time();

		foreach ($this->items as $item) {
			$text .= urlencode($item['name']).' = '.urlencode($item['value']).';';

			if ($item['expires'] < $time) {
				$text .= ' deleted;';
			}

			$text .= ' expires='.gmdate("D, d-M-Y H:i:s T", $item['expires']).';';

			if ($item['path'] && $item['path'] !== '/') {
				$text .= ' path='.$item['path'];
			}

			if ($item['domain']) {
				$text .= ' domain='.$item['domain'].';';
			}

			if ($item['secure']) {
				$text .= ' secure;';
			}

			if ($item['httponly']) {
				$text .= ' httponly;';
			}

			$text .= "\n";
		}

		return $text;
	}



	/**
	 * public function send ()
	 *
	 * Sends the cookies
	 * Returns boolean
	 */
	public function send () {
		if (headers_sent()) {
			return false;
		}

		foreach ($this->items as $cookie) {
			if (!setcookie($cookie['name'], $cookie['value'], $cookie['expire'], $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly'])) {
				return false;
			}
		}

		return true;
	}



	/**
	 * public function get ([string $name], [string $path], [string $domain])
	 *
	 * Gets one or all parameters
	 * Returns mixed
	 */
	public function get ($name = null, $path = '/', $domain = null) {
		if (func_num_args() === 0) {
			return $this->items;
		}

		return $this->items["$name $path $domain"];
	}



	/**
	 * public function set (string $name, [mixed $value], [int $expire], [string $path], [string $domain], [boolean $secure], [boolean $httponly])
	 * public function set (array $values)
	 *
	 * Sets one parameter
	 * Returns none
	 */
	public function set ($name, $value = null, $expire = 0, $path = '/', $domain = null, $secure = false, $httponly = true) {
		if (is_array($name)) {
			foreach ($name as $key => $value) {
				$this->set($key, $value);
			}

			return;
		}

		if ($expire instanceof \DateTime) {
			$expire = $expire->format('U');
		} else if (!is_numeric($expire)) {
			$expire = strtotime($expire);
		}

		$path = empty($path) ? '/' : $path;

		$this->items["$name $path $domain"] = array(
			'name' => $name,
			'value' => $value,
			'domain' => $domain,
			'expire' => $expire,
			'path' => empty($path) ? '/' : $path,
			'secure' => (Boolean)$secure,
			'httponly' => (Boolean)$httponly
		);
	}



	/**
	 * public function delete ([string $name], [string $path], [string $domain])
	 *
	 * Deletes one or all cookies
	 * Returns none
	 */
	public function delete ($name = null, $path = '/', $domain = null) {
		if (func_num_args() === 0) {
			foreach ($this->items as $cookie) {
				$this->set($cookie['name'], null, time() - 31536001, $cookie['path'], $cookie['domain']);
			}
		} else {
			$this->set($name, null, time() - 31536001, $path, $domain);
		}
	}



	/**
	 * public function clear ([string $name], [string $path], [string $domain])
	 *
	 * Clear one or all cookies in the object (not in the browser)
	 * Returns none
	 */
	public function clear ($name = null, $path = '/', $domain = null) {
		if (func_num_args() === 0) {
			$this->items = array();
		} else {
			unset($this->items["$name $path $domain"]);
		}
	}
}
?>