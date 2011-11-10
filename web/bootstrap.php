<?php
use Fol\Input;
use Fol\Output;

$Input = new Input();
$Output = new Output();

//Init the controller
$Router->go();

$Output->show();
?>