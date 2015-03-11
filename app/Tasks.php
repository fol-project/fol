<?php
namespace App;

use Fol\Tasks as FolTasks;

class Tasks extends FolTasks\Tasks
{
    public static $app;

    /**
     * Install the project
     * 
     * @param string  $env   The environment name
     * 
     * @option $force Whether or not overwrite the values
     */
    public function install($env = null, $opts = ['force|f' => false])
    {
        //Basic configuration
        $this->taskConfig(static::$app->config)
            ->environment($env)
            ->force($opts['force'])
            ->set('app')
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
