<?php
namespace Fol;

class Output {
	public $headers = array();
	public $content = '';



	/**
	 * public function setContent (string $content)
	 *
	 * Sets content to output
	 * Returns none
	 */
	public function setContent ($content) {
		$this->content = $content;
	}



	/**
	 * public function addContent (string $content)
	 *
	 * Adds content to output
	 * Returns none
	 */
	public function addContent ($content) {
		$this->content .= $content;
	}



	/**
	 * public function addHeader (string $header)
	 *
	 * Sets a headers to output
	 * Returns none
	 */
	public function addHeader ($header, $replace = true) {
		$this->headers[] = array($header, $replace);
	}



	/**
	 * public function setContentType (string $type, [string $charset])
	 *
	 * Sets the content type header to output
	 * Returns none
	 */
	public function setContentType ($type, $charset = 'UTF-8') {
		switch ($type) {
			case 'js':
			case 'json':
			case 'html':
			case 'css':
				$type = 'text/'.$type;
				break;
			
			case 'gif':
			case 'jpeg':
			case 'jpg':
			case 'png':
				$type = 'image/'.$type;
				break;

			case 'pdf':
			case 'zip':
				$type = 'application/'.$type;
				break;
			
			case 'txt':
				$type = 'text/plain';
				break;
		}

		$this->addHeader('Content-Type: '.$type.' '.$charset, true);
	}



	/**
	 * public function setCache (int $duration)
	 *
	 * Sets the cache headers to output
	 * Returns none
	 */
	public function setCache ($duration = 0) {
		if (!$duration) {
			$this->addHeader('Cache-Control: no-cache', true);
			$this->addHeader('Expires: -1', true);

			return;
		}

		$this->addHeader('Pragma: public', true);
		$this->addHeader('Expires: '.gmdate('D, d M Y H:i:s').' GMT', true);
	}



	/**
	 * public function forceDownload ([string $filename])
	 *
	 * Adds headers to force to download the file
	 * Returns none
	 */
	public function forceDownload ($filename = '') {
		$this->addHeader('Content-Type: application/force-download', true);
		$this->addHeader('Content-Description: File Transfer', true);

		if ($filename) {
			$this->addHeader('Content-Disposition: filename='.$filename);
		}
	}



	/**
	 * public function show (void)
	 *
	 * Outputs all data
	 * Returns none
	 */
	public function show () {
		foreach ($this->headers as $header) {
			header($header[0], $header[1]);
		}

		echo $this->content;
	}
}
?>