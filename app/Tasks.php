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
        $this->say("server started at ".getenv('FOLK_CLI_SERVER_URL')."\n");

        $this->taskServer(parse_url(getenv('FOLK_CLI_SERVER_URL'), PHP_URL_PORT) ?: 80)
            ->dir('public')
            ->arg('public/index.php')
            ->run();
    }
}
