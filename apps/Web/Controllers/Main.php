<?php
namespace Apps\Web\Controllers;

use Fol\Response;
use Fol\Controller;
use Fol\App;

class Main extends Controller {
	public function index () {
		$Response = new Response();
		$Response->Headers->setCache(array(
			'Expires' => '10-6-2012',
			'Last-Modified' => 'Sat, 28 Jan 2011 01:48:11 GMT',
			'max-age' => 3600,
			'private' => true
		));

		$cache = $Response->Headers->getCache();
		$cache['public'] = false;

		$Response->Headers->setCache($cache);
		
		//$Response->Headers->setDateTime('Expires', 'Sat, 28 Jan 2011 01:48:11 GMT');
		pre((string)$Response);
		
		return $Response;

		/*
		común entre response/request:

		- Cache-Control
		- Connection
		- Content-Length
		- Content-MD5
		- Content-Type
		- Date
		- Pragma
		- Warning

		relacionados:
		- Accept-Encoding -> Content-Encoding
		- Accept-Language -> Content-Language
		*/
	}

	public function testSpeed () {
		$repeticions = 10000;
		
		$time = microtime(true);

		for ($n = 0; $n < $repeticions; $n++) {
			include($this->App->path.'config/controllers.php');
		}

		echo '<p>Total caso 1: '.(microtime(true) - $time).'</p>';

		$time = microtime(true);

		for ($n = 0; $n < $repeticions; $n++) {
			parse_ini_file($this->App->path.'config/controllers.ini', true);
		}

		echo '<p>Total caso 2: '.(microtime(true) - $time).'</p>';
	}

	public function show ($section) {
		return new Response("ola $section, que tal");
	}
}
?>