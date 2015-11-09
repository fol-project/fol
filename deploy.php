<?php

//Use the same timezone on deploy from any machine
ini_set('date.timezone', 'Europe/Madrid');

require 'recipe/composer.php';

server('dev', 'example.com', 22)
    ->user('root')
    ->forwardAgent()
    ->stage('dev')
    ->env('branch', 'develop')
    ->env('deploy_path', '/var/www/examle.com');

set('repository', 'git@github.com:user/repo.git');
set('writable_dirs', ['public']);
set('shared_files', ['.env']);
set('shared_dirs', ['data']);

task('deploy:assets', function () {
    $path = env('release_path');

    runLocally('node node_modules/.bin/gulp');

    upload('public/img', $path.'/public/img');
    upload('public/css', $path.'/public/css');
    upload('public/js', $path.'/public/js');
});

task('deploy', [
    'deploy:prepare',
    'deploy:release',
    'deploy:update_code',
    'deploy:vendors',
    'deploy:writable',
    'deploy:shared',
    'deploy:assets',
    'deploy:symlink',
    'cleanup',
]);
