<?php
namespace App;

use Fol\Tasks as FolTasks;

class Tasks extends FolTasks\Tasks
{
    public static $app;

    /**
     * Update the project
     */
    public function update()
    {
        //Update dependencies
        $this->taskComposerInstall()->run();
        $this->taskNpmInstall()->run();
        $this->taskBowerInstall('node_modules/.bin/bower')->run();
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
