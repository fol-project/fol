<?php
namespace App;

use Fol\Tasks as FolTasks;

class Tasks extends FolTasks\Tasks
{
    public static $app;

    /**
     * Install the project
     *
     * @option $force Whether or not overwrite the values
     */
    public function install($env = null)
    {
        //Create logs and .env files
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
     */
    public function server()
    {
        $port = static::$app->config['app']['server_cli_port'];

        $this->say("server started at http://127.0.0.1:{$port}\n");

        $this->taskServer($port)
            ->dir('public')
            ->arg('public/index.php')
            ->run();
    }
}
