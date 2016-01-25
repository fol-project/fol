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
     * Run a php server.
     */
    public function run()
    {
        $env = [
            'APP_DEV' => 'true',
            'APP_URL' => 'http://127.0.0.1:8000',
        ];

        //php server
        $this->taskServer(parse_url($env['APP_URL'], PHP_URL_PORT))
            ->env($env)
            ->dir('public')
            ->arg('public/index.php')
            ->background()
            ->run();

        //gulp + browser sync
        $this->taskExec('node node_modules/.bin/gulp sync')
            ->env($env)
            ->run();
    }
}
