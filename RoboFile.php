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
        $url = env('APP_CLI_SERVER_URL');

        //php server
        $this->taskServer(parse_url($url, PHP_URL_PORT) ?: 80)
            ->env([
                'APP_DEV' => 'true',
            ])
            ->dir('public')
            ->arg('public/index.php')
            ->background()
            ->run();

        //gulp + browser sync
        $this->taskExec('node node_modules/.bin/gulp sync')
            ->env([
                'APP_URL' => $url,
                'APP_SYNC_PORT' => env('APP_SYNC_PORT'),
                'APP_DEV' => 'true',
            ])
            ->run();
    }
}
