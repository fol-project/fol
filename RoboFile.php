<?php

require __DIR__.'/bootstrap.php';

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends \Robo\Tasks
{
    /**
     * Install all npm and bower components.
     */
    public function install()
    {
        //logs folder
        if (!is_dir(__DIR__.'/data/log')) {
            mkdir(__DIR__.'/data/log', 0777, true);
        }

        //npm + bower
        //$this->taskNpmInstall()->run();
        //$this->taskBowerInstall('node_modules/.bin/bower')->run();
    }

    /**
     * Run a php server.
     */
    public function server()
    {
        $this->taskServer(parse_url(env('APP_CLI_SERVER_URL'), PHP_URL_PORT) ?: 80)
            ->dir('public')
            ->arg('public/index.php')
            ->run();
    }
}
