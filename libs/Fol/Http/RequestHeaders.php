<?php
/**
 * Fol\Http\RequestHeaders
 * 
 * Manage http headers in requests
 */
namespace Fol\Http;

class RequestHeaders extends Headers {
	/**
	 * Detects http header from a $_SERVER array
	 * 
	 * @param array $server The $_SERVER array
	 * 
	 * @return array The headers found
	 */
	public static function getHeadersFromServer (array $server) {
		$headers = array();

		foreach ($server as $name => $value) {
			if (strpos($name, 'HTTP_') === 0) {
				$headers[str_replace('_', '-', substr($name, 5))] = $value;
				continue;
			}

			if (in_array($name, array('CONTENT_LENGTH', 'CONTENT_MD5', 'CONTENT_TYPE'))) {
				$headers[str_replace('_', '-', $name)] = $value;
			}
		}

		if (isset($server['PHP_AUTH_USER'])) {
			$pass = isset($server['PHP_AUTH_PW']) ? $server['PHP_AUTH_PW'] : '';
			$headers['AUTHORIZATION'] = 'Basic '.base64_encode($server['PHP_AUTH_USER'].':'.$pass);
		}

		return $headers;
	}


	/**
	 * Defines a If-Modified-Since header
	 * 
	 * @param string/Datetime $datetime
	 */
	public function setIfModifiedSince ($datetime) {
		$this->setDateTime('If-Modified-Since', $datetime);
	}
}
?>
