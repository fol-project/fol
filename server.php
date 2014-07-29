<?php
if (($_SERVER['SCRIPT_NAME'] !== '/index.php') && (strpos($_SERVER['SCRIPT_NAME'], '/.') === false) && is_file($_SERVER['SCRIPT_FILENAME'])) {
	return false;
}

require __DIR__.'/public/index.php';
