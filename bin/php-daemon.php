<?php

ini_set('memory_limit', '128M');

if (file_exists(dirname(__FILE__) . '/../vendor/autoload.php')) {
    require_once dirname(__FILE__) . '/../vendor/autoload.php';
} elseif (file_exists(dirname(__FILE__) . '/../../../autoload.php')) {
    require_once dirname(__FILE__) . '/../../../autoload.php';
}

$app = new \PhpDaemon\PhpDaemon();
$app->run();
