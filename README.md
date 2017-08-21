# Php Daemon

This is an example on how to write a php based linux daemon on Ubuntu 16.04 LTS using the `posix` and `pcntl` extensions.
The daemon itself should be compatible to most linux distros. The daemon will be installed using `systemd` as service
manager (default for Ubuntu >= 15.04).

If you need to change the service manager, you just need to modify the `install.sh` script.

## Installation

Remember to modify your CLI `php.ini` and set `phar.readonly=Off` before building/installing:

```bash
    $ composer install
    $ composer build
    $ ./install.sh
```

Use `systemd` to controll the service:

```bash
    $ systemctl start php-daemon
    $ systemctl restart php-daemon
    $ systemctl stop php-daemon
    $ systemctl status php-daemon
```

## Debugging

As a systemd service you can use the `journal`:

```bash
    journalctl -xe -u php-daemon
```

Tail the logfiles to see what is going on while starting or stoping the service:

    tail -f /var/log/php-daemon*log
    
    Starting up
    Working Directory: /opt/php-daemon
    Parent Process 6581 Exiting
    Child Process 6582 Active
    
    ...
    
    SIGTERM (15): Cleaning up


Find the process id (Main PID) of the running service:

    $ systemctl status php-daemon
      php-daemon.service - PHP Daemon
       Loaded: loaded (/var/www/php-daemon/php-daemon.service; disabled; vendor preset: enabled)
       Active: active (running) since Son 2017-08-20 02:16:34 CEST; 5min ago
      Process: 6581 ExecStart=/opt/php-daemon/php-daemon (code=exited, status=0/SUCCESS)
     Main PID: 6582 (php)
       CGroup: /system.slice/php-daemon.service
               └─6582 php /opt/php-daemon/php-daemon

Or just:

```bash
    $ ps aux | grep php-daemon
    root      6582  100  0.1 386484 10800 ?        Rs   02:16  10:57 php /opt/php-daemon/php-daemon
```

## Custom Signal

Lets send `SIGUSR1` (10) to process ID `6582`.

Using bash:

```bash
    $ kill -10 6582
```

Using PHP/Posix:

```php
    posix_kill(6582, SIGUSR1);
```  

## Reports

As part of the built result, some reports and api documentations have been created:

    dist/report
    ├── apigen
    ├── lint
    ├── pdepend
    ├── phpcpd
    ├── phpcs
    ├── phpdocumentor
    ├── phploc
    └── phpmd

## TODO

 * Implement proper reloading via systemd service file (`systemctl reload php-daemon`)
 * Configure default file descriptors `stdin`, `stdout` and `stderr` via systemd service file