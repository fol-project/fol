<?php

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
    $uploads = [
        '/public/.htaccess',
        '/public/img',
        '/public/css',
        '/public/js',
    ];

    runLocally('node node_modules/.bin/gulp');

    foreach ($uploads as $dir) {
        upload(__DIR__.$dir, $path.$dir);
    }
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
