<?php

use function Deployer\{host, localhost, server, task, run, set, get, add, before, after};

require 'recipe/laravel.php';

///////////////////////////////////////////////////////////////////////////////
// define servers
///////////////////////////////////////////////////////////////////////////////

$servers = [
    'preprod' => [
        'stage'            => 'preprod',
        'host'             => '51.15.172.104',
        'user'             => 'mishgunn',
        'path'             => '/var/www/preprod',
        'http_user'        => 'mishgunn',
        'http_group'       => 'mishgunn',
        'private_identity' => '~/.ssh/id_rsa',
        'public_identity'  => '~/.ssh/id_rsa.pub',
        'repository'       => 'git@github.com:una-club/una.app.git',
        'branch'           => 'develop',
        'composer_options' => 'install --no-dev --verbose --prefer-dist --optimize-autoloader --no-progress --no-interaction',
    ],
    'prod'    => [
        'stage'            => 'production',
        'host'             => 'vps241083.ovh.net',
        'user'             => 'deploy',
        'path'             => '/var/www/prod/univ-nantes-aviron',
        'http_user'        => 'deploy',
        'http_group'       => 'deploy',
        'private_identity' => '~/.ssh/id_rsa',
        'public_identity'  => '~/.ssh/id_rsa.pub',
        'repository'       => 'git@github.com:una-club/una.app.git',
        'branch'           => 'master',
        'composer_options' => 'install --no-dev --verbose --prefer-dist --optimize-autoloader --no-progress --no-interaction',
    ],
    'docker-prod'    => [
        'stage'            => 'docker-prod',
        'host'             => 'localhost',
        'user'             => 'root',
        'path'             => '/var/www/prod/univ-nantes-aviron',
        'http_user'        => 'root',
        'http_group'       => 'root',
        'private_identity' => '~/.ssh/id_rsa',
        'public_identity'  => '~/.ssh/id_rsa.pub',
        'repository'       => 'https://github.com/una-club/una.app.git',
        'branch'           => 'master',
        'composer_options' => 'install --no-dev --verbose --prefer-dist --optimize-autoloader --no-progress --no-interaction',
    ]
];

///////////////////////////////////////////////////////////////////////////////
// configure servers
///////////////////////////////////////////////////////////////////////////////

// set configurations
set('default_timeout', 2000);
set('shared_files', ['.env']);
set('shared_dirs', [
    'storage/app',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
]);
set('writable_dirs', ['bootstrap/cache', 'storage']);
set('keep_releases', 5);
set('default_stage', 'preprod');
set('ssh_type', 'native');
set('ssh_multiplexing', true);

// configure servers
foreach ($servers as $server_env => $server) {
    if (!isset ($server['active']) || (isset($server['active']) && $server['active'])) {

      if($server['host'] == "localhost"){
        $serverCfg = localhost();
      }else{
        $serverCfg = host($server_env)
            ->hostname($server['host']);
      }

      $serverCfg
            ->user($server['user'])
            ->identityFile($server['public_identity'], $server['private_identity'], null)
            ->set('repository', $server['repository'])
            ->set('deploy_path', $server['path'])
            ->set('http_user', $server['http_user'])
            ->set('http_group', $server['http_group'])
            ->set('composer_options', $server['composer_options'])
            ->set('branch', $server['branch'])
            ->stage($server['stage']);
    }
}

///////////////////////////////////////////////////////////////////////////////
// configure tasks
///////////////////////////////////////////////////////////////////////////////

// project install script
task('project:install', function () {
    run('cd {{release_path}} && bash .utils/project_install.sh');
})->desc('Run install script from the root of the project');
before('deploy:symlink', 'project:install');

// laravel cron install
task('cron:install', function () {
    run('job="* * * * * php {{deploy_path}}/current/artisan schedule:run >> /dev/null 2>&1"; ct=$(crontab -l |grep -i -v "$job");(echo "$ct" ;echo "$job") |crontab -');
})->desc('Add the laravel cron to the others on the server');
after('project:install', 'cron:install');

// restart nginx and php
task('server:restart', function () {
    //run('service nginx reload');
    //run('service php7.0-fpm restart');
})->desc('Restart Nginx and PHP7.0 service');
after('success', 'server:restart');
