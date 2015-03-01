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
        //Basic configuration
        $this->taskConfig()
            ->set([
                'ENVIRONMENT' => 'development',
                'BASE_URL' => 'http://localhost/'.basename(dirname(__DIR__)).'/public',
            ], 'env.php')
            ->run();

        //Create log files
        $this->taskFileSystemStack()
            ->mkdir('data/log')
            ->touch('data/log/php')
            ->chmod('data/log/php', 0777)
            ->run();

        //npm + bower
        //$this->taskNpmInstall()->run();
        //$this->taskBowerInstall()->run();
    }

    /**
     * Run a php server
     * 
     * @param integer $port The port number
     */
    public function server($port = 8000)
    {
        $this->say("server started at http://127.0.0.1:{$port}\n");

        $this->taskServer($port)
            ->dir('public')
            ->arg('public/index.php')
            ->run();
    }
}
