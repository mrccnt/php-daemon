# Php Daemon

This is an example on how to write a php based linux daemon on Ubuntu 16.04 LTS. The daemon is written using the
Process Control Extensions [POSIX](http://php.net/manual/en/book.posix.php) and [PCNTL](http://php.net/manual/en/book.pcntl.php).
Ubuntu will controll the daemon via its default init manager `systemd`.

Most examples on the web (even on php.net; outdated as well) are basically simple commandline applications using
different arguments and some shell scripts starting/stoping them from `/etc/init.d`. This might work but is kind of
ugly. There is a more elegant ways to do all this.

By utilizing `systemd` and a single phar file we will create a daemon that is fully implemented in our Ubuntu system.
The daemon will listen to POSIX signals like `SIGTERM`, `SIGHUP`, etc. No shell scripts are needed to run the daemon.

## How does it work

When developing daemons/services, there are some rules that should be followed.

First thing should be that we are redirecting the standard file descriptors STDIN, STDOUT and STDERR. A daemon will
never read any inputs via STDIN so we are going to redirect STDIN to `/dev/null`. If we want to know what a daemon is
doing we will have to check the logs. So any outputs from STDOUT and STDERR should be redirected to a logfile.

Of yourse we need some kind of home- or working-directory. The daemon should always run in a save place. Optionally we
can create a specifc user and/or group for the daemon.

Now we need to set a file creation mask to define how permissions are set on new files. This way we dont have to take
care of it when creating files in code and we can make sure to define which permissions the process is allowed to set.

When a daemon is started, `systemd` expects a successful returncode (0) from the application (successful start of daemon).
This means the application (or process) needs to exit. But if we want to keep the daemon running in background (of
course...) we need to handle this. We are forking the current process. Now we have a parent process and a child process
running in parallel at the same point in code. To tell `systemd` that everything worked well we kill the parent process
`exit(0)`.

The new parent process (child) is up and running. Normally daemons create subprocesses theirselves to handle non blocking
jobs, but so far those subprocess would not get stopped if you do a `systemctl stop php-daemon`. We need to make the
current process the session/group leader. The subprocesses will then get stopped too.

We should not forget to set up a PID file containing the current process id.

## Systemd / PHP

The init manager and php need to handle different things:

PHP (class `PhpDaemon\PhpDaemon`):

 * Forking process
 * Handle parent/child process
 * Make process session leader
 * Write and lock PID file
 * Initialize a main loop
 * Listen to signals

Systemd (systemd control file `debian.control`):

 * Define file creation mask
 * Set user/group
 * Set working directory
 * Set binary and params
 * Define restart behaviour
 * Handle standard file descriptors
 * Handle logging
 * Remove PID file
 
## Building

Remember to modify your CLI `php.ini` and set `phar.readonly=Off` before building/installing:

```bash
    $ composer install
    $ composer build
```

## Installing

Install/Uninstall the daemon file directly via shell scripts:

```bash
    $ sudo ./install.sh
    $ sudo ./uninstall.sh
```

Or you can use the debian installer package:

```bash
    $ sudo dpkg -i dist/php-daemon.deb
    $ sudo dpkg -r php-daemon
```

Use `systemd` to controll the service:

```bash
    $ systemctl start php-daemon
    $ systemctl restart php-daemon
    $ systemctl stop php-daemon
    $ systemctl status php-daemon
```

## Debugging

As a systemd service you can use `journalctl` to check what is going on. Tail the logfiles using the `-f` argument to
see what is going on while starting or stoping the service:

```bash
    journalctl -f -u php-daemon
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

## TODO:

 * Replace simpe debian build mechanism and do not use `dpkg -b`