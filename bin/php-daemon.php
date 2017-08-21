<?php

if (file_exists(dirname(__FILE__) . '/../vendor/autoload.php')) {
    require_once dirname(__FILE__) . '/../vendor/autoload.php';
} elseif (file_exists(dirname(__FILE__) . '/../../../autoload.php')) {
    require_once dirname(__FILE__) . '/../../../autoload.php';
}

fclose(STDIN);
fclose(STDOUT);
fclose(STDERR);

$STDIN = fopen('/dev/null', 'r');
$STDOUT = fopen('/var/log/php-daemon.log', 'ab');
$STDERR = fopen('/var/log/php-daemon.error.log', 'ab');

ini_set('error_log', '/var/log/php-daemon.error.log');

umask(133);

echo PHP_EOL . 'Starting up' . PHP_EOL;
echo 'Working Directory: ' . getcwd() . PHP_EOL;

$app = new \PhpDaemon\PhpDaemon();
$app->run();
