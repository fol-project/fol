<?php
namespace Apps\Web\Controllers;

use Fol\Response;
use Fol\Controller;

class Main extends Controller {
	public function index () {
		/*
		function convert ($size) {
			$unit = array('b','kb','mb','gb','tb','pb');

			return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
		}

		return new Response('Ola mundo - en '.convert(memory_get_peak_usage(true)));
		*/
		return new Response(memory_get_peak_usage(true));
	}
}
?>