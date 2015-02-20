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
                'BASE_URL' => 'http://localhost/'.basename(dirname(__DIR__)).'/public',
            ], 'env.php')
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
        $this->say("server started at http://localhost:{$port}");

        $this->taskExec("php -S localhost:{$port} -t public public/index.php")->run();
    }
}
