<?php
/**
 * Fol\Http\Cookies
 * 
 * Class to manage cookies
 */
namespace Fol\Http;

class Cookies {
	protected $items = array();


	/**
	 * Magic function to converts all cookies to a string
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
	 * Send the cookies to the browser
	 * 
	 * @return boolean True if all cookies have sent or false on error or if headers have been sent before
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
	 * Gets one or all cookies
	 * 
	 * @param string $name The cookie name
	 * @param string $path The cookie path
	 * @param string $domain The cookie domain
	 * 
	 * @return array The cookie data or null
	 */
	public function get ($name = null, $path = '/', $domain = null) {
		if (func_num_args() === 0) {
			return $this->items;
		}

		return $this->items["$name $path $domain"];
	}



	/**
	 * Sets a new cookie
	 * 
	 * @param string $name The cookie name
	 * @param string $value The cookie value
	 * @param mixed $expire The cookie expiration time. It can be a number or a DateTime instance
	 * @param string $domain The cookie domain
	 * @param boolean $secure If the cookie is secure, only will be send in secure connection (https)
	 * @param boolean $httponly If is set true, the cookie only will be accessed via http, so javascript cannot access to it.
	 */
	public function set ($name, $value = null, $expire = 0, $path = '/', $domain = null, $secure = false, $httponly = true) {
		if (is_array($name)) {
			foreach ($name as $name => $value) {
				$this->set($name, $value);
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
	 * Deletes one or all cookies
	 * 
	 * @param string $name The cookie name
	 * @param string $path The cookie path
	 * @param string $domain The cookie domain
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
	 * Clear one or all cookies in the object (not in the browser)
	 * 
	 * @param string $name The cookie name
	 * @param string $path The cookie path
	 * @param string $domain The cookie domain
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