<?php
use Fol\Input;
use Fol\Output;
use Fol\Cache;

$Cache = new Cache();

if (!$Cache->getPage($Output)) {
	$Input = new Input();
	$Output = new Output();

	$Router->go();
}

$Output->show();
?>