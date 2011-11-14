<?php
use Fol\Input;
use Fol\Output;

$Input = new Input();
$Output = new Output();

$Router->go();

$Output->show();
?>