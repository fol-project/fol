<?php
/**
 * Fol\Utils\UploadsModel
 * 
 * Provides basic upload operations (save files from user upload or url).
 * Example:
 * 
 * class Items {
 * 	use Fol\Utils\UploadsModel;
 * }
 * 
 * Item::setUploadsFolder('/uploadsfolder/');
 * 
 * $Item = new Item();
 */
namespace Fol\Utils;

trait UploadsModel {
	protected static $uploadsPath;


	/**
	 * static function to configure the upload folder.
	 * 
	 * @param string $folder The folder path
	 */
	public static function setUploadsFolder ($folder) {
		static::$uploadsPath = $folder;
	}


	/**
	 * Save a file
	 * 
	 * @param string/array $file The file to save. Can be from $_FILES or url
	 * @param string $path The path for the file (starting from uploads folder)
	 * @param string $filename An optional new filename
	 * 
	 * @return string The upload filename.
	 * @return null If no file has been uploaded
	 * @return false If there was an error
	 */
	public function saveFile ($file, $path, $filename = null) {
		if (is_array($file)) {
			return $this->saveUploadedFile($file, $path, $filename);
		}
		if (is_string($file)) {
			return $this->saveFileFromUrl($file, $path, $filename);
		}
	}

	
	/**
	 * Save an uploaded file
	 * 
	 * @param array $file The uploaded file (from $_FILES array)
	 * @param string $path The path for the file (starting from uploads folder)
	 * @param string $filename An optional new filename
	 * 
	 * @return string The upload filename.
	 * @return null If no file has been uploaded
	 * @return false If there was an error
	 */
	public function saveUploadedFile (array $file, $path, $filename = null) {
		if (!empty($file['tmp_name']) && empty($file['error'])) {
			if ($filename === null) {
				$filename = $file['name'];
			}

			if (!pathinfo($filename, PATHINFO_EXTENSION) && ($extension = pathinfo($file['name'], PATHINFO_EXTENSION))) {
				$filename .= ".$extension";
			}

			$destination = static::$uploadsPath.$path.$filename;

			if (!rename($file['tmp_name'], $destination)) {
				return false;
			}

			chmod($destination, 0666);

			return $filename;
		}
	}


	/**
	 * Save a file from an URL
	 * 
	 * @param string $file The url of the file
	 * @param string $path The path for the file (starting from uploads folder)
	 * @param string $filename An optional new filename
	 * 
	 * @return string The upload filename.
	 * @return null If no file has been uploaded
	 * @return false If there was an error
	 */
	public function saveFileFromUrl ($file, $path, $filename = null) {
		if (!empty($file) && strpos($file, '://')) {
			if ($filename === null) {
				$filename = pathinfo($file, PATHINFO_BASENAME);
			} else if (!pathinfo($filename, PATHINFO_EXTENSION) && ($extension = pathinfo($file, PATHINFO_EXTENSION))) {
				if (strpos($extension, '?') !== false) {
					$extension = explode('?', $extension)[0];
				}

				$filename .= ".$extension";
			}

			$destination = static::$uploadsPath.$path.$filename;

			try {
				$content = file_get_contents($file);
				file_put_contents($destination, $content);
			} catch (\Exception $Exception) {
				return false;
			}

			return $filename;
		}
	}
}
?>
