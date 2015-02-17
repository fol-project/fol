<?php
namespace App;

use Fol\Tasks as FolTasks;

class Tasks extends FolTasks\Tasks
{
	public static $app;

	/**
	 * Install all npm and bower components
	 */
	public function install()
	{
		$this->taskEnvironmentVariables()->run();

		//npm + bower
		//$this->taskNpmInstall()->run();
		//$this->taskBowerInstall()->run();
	}

	/**
	 * Run a php server
	 */
	public function server()
	{
		$this->say("server started at http://localhost:8000");

		$this->taskServer(8000)
			->dir('public')
			->run();
	}
}
