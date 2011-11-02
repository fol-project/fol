<?php
/*
 * function __autoload ($class_name)
 */
function __autoload ($class_name) {
	$path = explode('\\', $class_name);
	$file = array_pop($path);

	if ($file[0] == 'i') {
		$file = 'interface_'.strtolower(substr($file, 1)).'.php';
	} else {
		$file = $file.'.php';
	}

	if ($path[0] === 'Controllers') {
		array_unshift($path, 'web');
	} else {
		array_splice($path, 1, 0, 'includes');
	}

	$file = implode('/', $path).'/'.$file;

	if (is_file(BASE_PATH.$file)) {
		include_once (BASE_PATH.$file);
	}
}

spl_autoload_register('__autoload');

/*
 * function __ ($text, [$args = null], [$null = false])
 *
 * return string
 */
function __ ($text, $args = null, $null = false) {
	static $Gettext = null;

	if (is_null($Gettext)) {
		$Gettext = getGettextObject();
	}

	$text = is_object($Gettext) ? $Gettext->translate($text, $null) : $text;

	if (is_null($args)) {
		return $text;
	} else if (is_array($args)) {
		return vsprintf($text, $args);
	} else {
		$args = func_get_args();

		array_shift($args);

		return vsprintf($text, $args);
	}
}

/*
 * function __e ($text)
 *
 * echo string
 */
function __e ($text, $args = null) {
	if (count(func_get_args()) > 2) {
		$args = func_get_args();

		array_shift($args);
	}

	echo __($text, $args);
}


/*
 * function hasText (string $text)
 *
 * Check if a string has any text value (no spaces, line breaks or html tags)
 *
 * return boolean
 */
function hasText ($text) {
	return trim(strip_tags($text)) ? true : false;
}


/*
 * function isNumericalArray (array $array)
 *
 * Return true if the array is numerical or false if it's asociative
 *
 * return boolean
 */
function isNumericalArray ($array) {
	if (is_array($array)) {
		return preg_match('/^[0-9]+$/', implode('', array_keys($array)));
	} else {
		return false;
	}
}


/*
 * function isMultidimensionalArray (array $array)
 *
 * Return true if all values of the array are subarrays
 *
 * return boolean
 */
function isMultidimensionalArray ($array) {
	if (!is_array($array)) {
		return false;
	}

	foreach ($array as $value) {
		if (!is_array($value)) {
			return false;
		}
	}

	return true;
}


/*
 * function absolutePath ([string/bool arg1], [string/bool arg2], [...])
 *
 * Return string
 */
function absolutePath () {
	$protocol = ($_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';

	return $protocol.getenv('SERVER_NAME').path(array('args' => func_get_args()));
}


/*
 * function path ([string/bool arg1], [string/bool arg2], [...])
 *
 * Return string
 */
function path () {
	global $Vars, $Config;

	$args = func_get_args();
	$options = array();

	if (is_array($args[0])) {
		$options = array_shift($args);

		if ($options['args']) {
			$args = $options['args'];
		}
	}

	if (isset($options['scene']) || isset($options['module'])) {
		if (!array_key_exists('scene', $options) || !$Config->scenes[$options['scene']]) {
			$options['scene'] = $Vars->getScene();
		}

		$path = BASE_WWW.(($Config->scenes[$options['scene']]['detect'] == 'subfolder') ? $options['scene'].'/' : '');

		if (array_key_exists('module', $options)) {
			if ($options['module'] && $Config->scenes[$options['scene']]['modules'][$options['module']]) {
				$path .= (($Config->scenes[$options['scene']]['modules'][$options['module']]) ? MODULE_WWW_SUBFOLDER.'/'.$options['module'].'/' : '');
			}
		} else if ($Vars->getModule()) {
			$path .= MODULE_WWW_SUBFOLDER.'/'.$Vars->getModule().'/';
		}
	} else {
		$path = $Vars->getModule() ? MODULE_WWW : SCENE_WWW;
	}

	if ($Config->languages['detect'] == 'subfolder') {
		if (!array_key_exists('language', $options)) {
			$path .= $Vars->getLanguage().'/';
		} else if ($options['language']) {
			if ($Config->languages['availables'][$options['language']]) {
				$path .= $options['language'].'/';
			}
		}
	}

	if (array_key_exists('exit_mode', $options)) {
		if ($options['exit_mode'] && $Config->exit_modes[$options['exit_mode']]) {
			$path .= $options['exit_mode'].'/';
		}
	} else if ($Config->exit_modes[$Vars->getExitMode()]['lock']) {
		$path .= $Vars->getExitMode().'/';
	}

	if (!$args) {
		if ($Vars->path) {
			if ($Vars->path[0] != 'index' || $Vars->path[1]) {
				$path .= implode('/', $Vars->getPath()).'/';
			}
		}
	} else {
		$n = 0;

		while ($args) {
			$arg = array_shift($args);

			if ($arg === true && $Vars->path[$n]) {
				$path .= $Vars->path[$n].'/';
			} else if (strlen($arg) && $arg !== false) {
				$path .= $arg.'/';
			}

			$n++;
		}
	}

	if ($options['host']) {
		$path = host().$path;
	}

	return $path;
}

/**
 * function referer (string $default, [boolean $redirect], [string $disabled])
 *
 * Return the string or redirect to previous page
 *
 * return string
 */
function referer ($default, $redirect = true, $disabled = '') {
	if (!is_array($default)) {
		$default = array($default);
	}

	if (!$disabled) {
		$disabled = path('users', 'login');
	}

	$referer = parse_url(getenv('HTTP_REFERER'));
	$request = getenv('REQUEST_URI');
	$url = '';

	if (!$referer['host'] || !$referer['path'] || ($referer['host'] != getenv('SERVER_NAME'))) {
		foreach ($default as $default_value) {
			if ($default_value != $request) {
				$url = $default_value;
				break;
			}
		}
	} else if (($referer['path'] != $request) && (!$disabled || !strstr($referer['path'], $disabled))) {
		$url = $referer['path'].($referer['query'] ? ('?'.$referer['query']) : '');
	} else {
		foreach ($default as $default_value) {
			if ($default_value != $request) {
				$url = $default_value;
				break;
			}
		}
	}

	if (!$url && ($request != path(''))) {
		$url = path('');
	}

	$url or die();

	if ($redirect) {
		redirect($url);
	} else if ($url) {
		return $url;
	}
}

/**
 * function filePath (string $path)
 *
 * return the correct path of the file
 *
 * return string
 */
function filePath ($path) {
	if ($path[0] == '/') {
		return $path;
	}

	global $Config, $Vars;

	preg_match('#(([\w-]+)/)?([\w-]+)(\|(.*))?#', $path, $matches);

	$context = $matches[2] ? $matches[2] : ($Vars->getModule() ? 'module' : 'scene');
	$basedir = $matches[3];
	$path = $matches[5];

	switch ($context) {
		case 'module':
			return fixPath(MODULE_PATH.$Config->module_paths[$basedir].$path);

		case 'phpcan':
			return fixPath(BASE_PATH.$Config->phpcan_paths[$basedir].$path);

		default:
			return fixPath(SCENE_PATH.$Config->scene_paths[$basedir].$path);
	}
}


/*
 * function fileWeb (string $path, [boolean $dinamic], [boolean $full])
 *
 * return the correct path of the file
 *
 * return string
 */
function fileWeb ($path, $dinamic = false, $host = '') {
	if ($path[0] == '/' || parse_url($path, PHP_URL_SCHEME)) {
		return $path;
	}

	global $Config, $Vars;

	if ($host === true) {
		$host = (getenv('HTTPS') == 'on') ? 'https://' : 'http://';

		if (getenv('SERVER_PORT') != 80) {
			$host .= getenv('SERVER_NAME').':'.getenv('SERVER_PORT');
		} else {
			$host .= getenv('SERVER_NAME');
		}
	}

	preg_match('#(([\w-]+)/)?([\w-]+)(\|(.*))?#', $path, $matches);

	$context = $matches[2] ? $matches[2] : ($Vars->getModule() ? 'module' : 'scene');
	$basedir = $matches[3];
	$path = $matches[5];

	if ($dinamic) {
		if (strpos($path, '$') === false) {
			$path = '$'.$path;
		}

		if ($Vars->getModule()) {
			return fixPath($host.MODULE_WWW.$context.'/'.$basedir.'/'.$path);
		}
		
		return fixPath($host.SCENE_WWW.$context.'/'.$basedir.'/'.$path);
	}

	switch ($context) {
		case 'module':
			return fixPath($host.MODULE_REAL_WWW.$Config->module_paths[$basedir].$path);

		case 'phpcan':
			return fixPath($host.BASE_WWW.$Config->phpcan_paths[$basedir].$path);

		default:
			return fixPath($host.SCENE_REAL_WWW.$Config->scene_paths[$basedir].$path);
	}
}


/*
 * function fixPath (string $path)
 *
 * resolve '//' or '/./' or '/foo/../' in a path
 *
 * Return string
 */
function fixPath ($path) {
	$replace = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#');

	do {
		$path = preg_replace($replace, '/', $path, -1, $n);
	} while ($n > 0);

	return $path;
}


/*
 * function get ([string $name], [string $value], [bool $add_all_get_variables])
 * function get ([array $values], [bool $add_all_get_variables])
 *
 * Return string
 */
function get ($name = null, $value = true, $add_all_get_variables = true) {
	global $Vars;

	$get = http_build_query($Vars->getGetVars($name, $value, $add_all_get_variables));

	return $get ? '?'.$get : '';
}


/**
 * function redirect ([string $url])
 *
 * URI redirect
 */
function redirect ($url = null) {
	global $Vars, $Debug;

	if (is_null($url)) {
		$url = path().get();
	}

	if ($Vars->message['outbox']) {
		$Vars->setCookie('phpcan_message', $Vars->message['outbox'], 10);
		$Vars->setCookie('phpcan_message_type', $Vars->message['type'], 10);
	}

	if ($Data && $Data->actions) {
		$Vars->setCookie('phpcan_executed_actions', serialize($Data->actions), 10);
	}

	if (headers_sent($file, $line)) {
		if (!$Debug->settings['redirect']) {
			$Debug->e('misc', 'Cannot redirect to "'.$url.'" because headers have been sent in "'.$file.'" (line '.$line.')');
		} else {
			$Debug->error('misc', 'Cannot redirect to "'.$url.'" because headers have been sent in "'.$file.'" (line '.$line.')');
		}
	} else if (!$Debug->settings['redirect']) {
		$Debug->e($url, __('Redirect'));
	} else {
		header('Location: '.$url);
	}

	exit;
}


/**
 * function includeFile (string $file, [array $data_content], [boolean $once])
 *
 * return boolean
 */
function includeFile ($file, $data_content = array(), $once = false) {
	if (!$file || !is_file($file)) {
		return;
	}

	global $Config;

	foreach ((array)$Config->config['autoglobal'] as $each) {
		global $$each;
	}

	if ($data_content) {
		extract($data_content, EXTR_SKIP);
	}

	if ($once) {
		return include_once($file);
	} else {
		return include($file);
	}
}


/**
 * function getDatabaseObject ([string $connection])
 *
 * return false/object
 */
function getDatabaseObject ($connection = null) {
	global $Config;

	if (!$Config->db) {
		return false;
	}

	if (is_null($connection)) {
		foreach ($Config->db as $conn => $settings) {
			if ($settings['default']) {
				$connection = $conn;
				break;
			}
		}
	}

	if (!($settings = $Config->db[$connection])) {
		return false;
	}

	switch ($settings['type']) {
		case 'mysql':
		return new \data\databases\Mysql($connection);
	}

	return false;
}


/**
 * function getImageObject ()
 *
 * return false/object
 */
function getImageObject () {
	global $Config;

	switch ($Config->images['library']) {
		case 'imagick':
		return new \files\images\Imagick;

		default:
		return new \files\images\Gd;
	}
}


/**
 * function getGettextObject ([string $time], [string $timezone])
 *
 * return false/object
 */
function getDatetimeObject ($time = null, $timezone = null) {
	return new \i18n\Datetime($time, $timezone);
}


/**
 * function getGettextObject ([string $language], [array $folders])
 *
 * return false/object
 */
function getGettextObject ($language = '', $folders = '') {
	if (!$language) {
		global $Vars;

		if (!($language = $Vars->getLanguage())) {
			return false;
		}
	}

	if (!$folders) {
		$folders = array(
			filePath('phpcan/languages|'),
			filePath('languages|'),
		);
	}

	foreach ((array)$folders as $folder) {
		$folder .= $language;

		if (is_dir($folder)) {
			$language_files = glob($folder.'/*.mo');

			if ($language_files) {
				if (!is_object($Gettext)) {
					$Gettext = new \i18n\Gettext;
				}

				foreach ($language_files as $each) {
					$Gettext->load($each);
				}
			}
		}
	}

	return $Gettext ? $Gettext : false;
}


/**
 * function alphaNumeric (string $text, [array/string $allow])
 *
 * Return string
 */
function alphaNumeric ($text, $allow = '') {
	$text = trim(strip_tags($text));

	$replace = array(
		'á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u',
		'à'=>'a','è'=>'e','ì'=>'i','ò'=>'o','ù'=>'u',
		'â'=>'a','ê'=>'e','î'=>'i','ô'=>'o','û'=>'u',
		'ä'=>'a','ë'=>'e','ï'=>'i','ö'=>'o','ü'=>'u',
		'Á'=>'a','É'=>'e','Í'=>'i','Ó'=>'o','Ú'=>'u',
		'À'=>'a','È'=>'e','Ì'=>'i','Ò'=>'o','Ù'=>'u',
		'Â'=>'a','Ê'=>'e','Î'=>'i','Ô'=>'o','Û'=>'u',
		'Ä'=>'a','Ë'=>'e','Ï'=>'i','Ö'=>'o','Ü'=>'u',
		'ñ'=>'n','Ñ'=>'n','ç'=>'c','Ç'=>'c',' '=>'_'
	);

	if ($allow) {
		$expr = '[^\w';

		if (is_string($allow)) {
			$expr .= preg_quote($allow, '/');
		} else if (is_array($allow)) {
			foreach ($allow as $from => $to) {
				if (is_string($from)) {
					$replace[$from] = $to;
				}

				if ($to) {
					$expr .= preg_quote($to, '/');
				}
			}
		}

		$expr .= ']';
	} else {
		$expr = '\W';
	}

	$text = strtolower(preg_replace('/'.$expr.'/', '', strtr($text, $replace)));

	return preg_replace('/\-+/', '-', $text);
}


/**
 * function arrayKeyValues (array $array, string $key, string $recursive)
 *
 * Return array
 */
function arrayKeyValues ($array, $key, $recursive = '') {
	if (!is_array($array)) {
		return array();
	}

	$return = array();

	if (isNumericalArray($array)) {
		foreach ($array as $value) {
			$return = array_merge($return, arrayKeyValues($value, $key, $recursive));
		}

		return $return;
	}

	if (array_key_exists($key, $array)) {
		$return[] = $array[$key];
	}

	if ($recursive && is_array($array[$recursive]) && $array[$recursive]) {
		$return = array_merge($return, arrayKeyValues($array[$recursive], $key, $recursive));
	}

	return $return;
}


/*
 * function arrayMergeReplaceRecursive (array $array1, array $array2, [array $array3], ...)^
 *
 * return array
 */
function arrayMergeReplaceRecursive () {
	$params = func_get_args();

	$return = array_shift($params);

	foreach ($params as $array) {
		if (!is_array($array)) {
			continue;
		}

		foreach ($array as $key => $value) {
			if (is_numeric($key) && (!in_array($value, $return))) {
				if (is_array($value)) {
					$return[] = arrayMergeReplaceRecursive($return[$$key], $value);
				} else {
					$return[] = $value;
				}
			} else {
				if (isset($return[$key]) && is_array($value) && is_array($return[$key])) {
					$return[$key] = arrayMergeReplaceRecursive($return[$key], $value);
				} else {
					$return[$key] = $value;
				}
			}
		}
	}

	return $return;
}

/*
 * function arrayMergeReplaceRecursiveStrict (array $array1, array $array2, [array $array3], ...)^
 *
 * return array
 */
function arrayMergeReplaceRecursiveStrict () {
	$params = func_get_args();

	$return = array_shift($params);

	foreach ($params as $array) {
		if (!is_array($array)) {
			continue;
		}

		foreach ($array as $key => $value) {
			if (isset($return[$key]) && is_array($value) && is_array($return[$key])) {
				$return[$key] = arrayMergeReplaceRecursiveStrict($return[$key], $value);
			} else {
				$return[$key] = $value;
			}
		}
	}

	return $return;
}


/**
 * function urlInfo (string $url)
 *
 * return array
 */
function urlInfo ($url) {
	$url = trim($url);

	if (!$url) {
		return array();
	}

	if (strpos($url, '://') === false) {
		$url = 'http://'.$url;
	}

	$info = parse_url($url);
	$info['url'] = $url;

	if ($info['query']) {
		parse_str($info['query'], $info['query']);
	} else {
		$info['query'] = array();
	}

	$info += pathinfo($info['path']);
	$info['path'] = explodeTrim('/', $info['path']);

	return $info;
}


/**
 * function encrypt (string $text)
 *
 * Text encryption
 *
 * return string
 */
function encrypt ($text) {
	if (function_exists('mcrypt_encrypt')) {
		global $Config;

		return trim(str_replace('/', '|', base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $Config->key, $text, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)))));
	} else {
		return trim(str_replace('/', '|', base64_encode($text)));
	}
}


/**
 * function decrypt (string $text)
 *
 * Text decryption
 *
 * return string
 */
function decrypt ($text) {
	if (function_exists('mcrypt_encrypt')) {
		global $Config;

		return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $Config->key, base64_decode(str_replace(array('|', ' '), array('/', '+'), $text)), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));
	} else {
		return trim(base64_decode(str_replace(array('|', ' '), array('/', '+'), $text)));
	}
}


/**
 * function textCutter (string $text, [int/string $limit], [string $end])
 *
 * Return string
 */
function textCutter ($text, $limit = 140, $end = '...') {
	if (is_int($limit)) {
		if (strlen($text) <= $limit) {
			return $text;
		}
	} else {
		$limit = mb_strpos($text, $limit);

		if ($limit === false) {
			return $text;
		}
	}

	$length = strlen($text);
	$num = 0;
	$tag = 0;

	for ($n = 0; $n < $length; $n++) {
		if ($text[$n] === '<') {
			$tag++;
			continue;
		}

		if ($text[$n] === '>') {
			$tag--;
			continue;
		}

		if ($tag === 0) {
			$num++;

			if ($num >= $limit) {
				$text = substr($text, 0, $n);
				$space = strrpos($text, ' ');

				if ($space) {
					$text = substr($text, 0, $space);
				}
				break;
			}
		}
	}

	if (strlen($text) == $length) {
		return $text;
	}

	$text .= $end;

	if (preg_match_all('|(<([\w]+)[^>]*>)|', $text, $aBuffer)) {
		if (!empty($aBuffer[1])) {
			preg_match_all("|</([a-zA-Z]+)>|", $text, $aBuffer2);

			if (count($aBuffer[2]) != count($aBuffer2[1])) {
				$closing_tags = array_diff($aBuffer[2], $aBuffer2[1]);
				$closing_tags = array_reverse($closing_tags);

				foreach($closing_tags as $tag) {
					$text .= '</'.$tag.'>';
				}
			}
		}
	}

	return $text;
}

/**
 * function explodeTrim (string $delimiter, string $text, [int $limit], [boolean $empty])
 *
 * Return string
 */
function explodeTrim ($delimiter, $text, $limit = null, $empty = false) {
	$return = array();

	$explode = is_null($limit) ? explode($delimiter, $text) : explode($delimiter, $text, $limit);

	foreach ($explode as $text_value) {
		$text_value = trim($text_value);

		if ($empty || ($text_value !== '')) {
			$return[] = $text_value;
		}
	}

	return $return;
}



/**
 * function encodeAscii (string $string)
 *
 * returns the ascii value of a string
 *
 * returns string
 */
function encodeAscii ($string) {
	$return = '';
	$length = strlen($string);

	for ($i = 0; $i < $length; $i++) {
		$return .= '&#'.ord($string[$i]).';';
	}

	return $return;
}


function camelCase ($string, $upper_first = false) {
	$string = str_replace('-', ' ', $string);
	$string = str_replace(' ', '', ucwords($string));

	if (!$upper_first) {
		return lcfirst($string);
	}

	return $string;
}