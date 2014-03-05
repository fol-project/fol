<?php
namespace App;

use Fol\Terminal;
use Fol\Http\Request;

class Cli {
	public static function execute (array $argv) {
		$fnName = $argv[1];

		if (method_exists('App\\Cli', $fnName)) {
			return (new static())->$fnName($argv);
		}

		if (in_array($fnName, ['GET', 'POST', 'HEAD', 'PUT', 'DELETE'])) {
			list($path, $params) = Terminal::parseOptions($argv, [2 => Terminal::OPTION_REQUIRED]);

			$request = Request::create(parse_url(BASE_URL, PHP_URL_PATH).$path[2], $path[1], $params);

			$app = new App();
			$app($request)->send();

			die();
		}

		throw new \Exception('No valid command');
	}
}
