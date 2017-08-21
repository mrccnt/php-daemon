#!/usr/bin/env php
<?php

try {
    Phar::mapPhar('php-daemon.phar');
    include 'phar://php-daemon.phar/bin/php-daemon.php';
} catch (PharException $e) {
    echo $e->getMessage();
    echo 'Cannot initialize Phar';
    exit(1);
}

__HALT_COMPILER();
