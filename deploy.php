<?php
namespace Deployer;

require 'recipe/symfony.php';

// Config
set('repository', 'git@github.com:rsteuber/gh.git');
set('git_tty', false);
set('ssh_mutliplexing', false);
set('composer_options', '{{composer_action}} --prefer-dist --no-progress --no-interaction --no-dev --optimize-autoloader');

add('shared_files', []);
add('shared_dirs', []);
add('writable_dirs', []);

set('allow_anonymous_stats', false);

// Hosts
host('68.183.5.65')
    ->set('remote_user', 'root')
    ->set('deploy_path', '/var/www/gh.top-feest.nl');

// Tasks
task('symlink:public', function() {
    run('ln -s {{release_path}}/public/*  /www &&  ln -s {{release_path}}/public/.[^.]* /www');
});

task('cache:clear', function () {
    run('php {{release_path}}/bin/console cache:clear');
});

/* Is used when symlink from public folder doesn't behave as expected.
 * The downside of using it this way is that it doesn't remove files no longer present in git repo.
 * Assumed public directory is /www
 */
task('copy:public', function() {
    run('cp -R {{release_path}}/public/*  /www && cp -R {{release_path}}/public/.[^.]* /www');
});

/* Uploads built assets from local to remote. Requires rsync.
 * Useful when you use Symfony encore/webpack and remote machine doesn't support npm/yarn.
 */
task('upload:build', function() {
    upload("public/build/", '{{release_path}}/public/build/');
});

task('upload:build', function() {
    upload("public/build/", '{{release_path}}/public/build/');
});

task('init:database', function() {
    run('{{bin/php}} {{bin/console}} doctrine:schema:create');
});

task('echo:options', function() {
    writeln('OPTIONS: {{composer_options}}');
});

task('build', function () {
    run('cd {{release_path}} && build');
});

task('initialize', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:unlock'
]);

task('gh_deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:vendors',
    'deploy:cache:clear',
    'deploy:cache:warmup',
    'deploy:symlink',
    'copy:public',
    'deploy:unlock',
]);

// Hooks
after('deploy:failed', 'deploy:unlock');
