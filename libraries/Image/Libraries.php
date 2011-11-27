<?php
//Original code from phpCan Image class (http://idc.anavallasuiza.com/)

namespace Image;

interface Libraries {
	public function load ($image);
	public function unload ();
	public function save ($filename = '');
	public function resize ($width, $height = 0, $enlarge = false);
	public function crop ($width, $height, $x = 0, $y = 0);
	public function flip ();
	public function flop ();
	public function zoomCrop ($width, $height);
	public function toString ();
	public function getMimeType ();
	public function rotate ($degrees, $background = null);
	public function merge ($image, $x = 0, $y = 0);
	public function convert ($format);
}
?>