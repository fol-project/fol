<?php
namespace App;

use Fol\Tasks as FolTasks;

class Tasks extends FolTasks\Tasks
{
	public static $app;

	/**
	 * Install the project
	 */
	public function install()
	{
		$this->taskConfig()
			->set([
				'ENVIRONMENT' => 'development',
  				'BASE_URL' => 'http://localhost',
			],'env.php')
			->run();

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
