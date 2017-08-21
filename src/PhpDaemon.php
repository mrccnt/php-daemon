<?php

namespace PhpDaemon;

use Pimple\Container;
use Psr\Container\ContainerInterface;

/**
 * Class PhpDaemon
 *
 * Read more about pcntl signals, posix sessions and file locks in the manuals.
 *
 * @link http://php.net/manual/en/function.pcntl-signal-dispatch.php
 * @link http://php.net/manual/en/function.pcntl-signal.php
 * @link http://php.net/manual/en/function.pcntl-fork.php
 * @link http://php.net/manual/en/function.posix-setsid.php
 * @link http://php.net/manual/en/function.flock.php
 *
 * @package PhpDaemon
 */
class PhpDaemon
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function __construct()
    {
        $this->fork();
        $this->lead();

        $this->container = new Container();

        /**
         * @param ContainerInterface $container
         * @return bool|resource
         */
        $this->container['pid'] = function ($container) {
            return fopen('/var/run/php-daemon.pid', 'c');
        };
    }

    /**
     * Register handlers to signals and enter main loop
     */
    public function run()
    {
        $this->setPid();

        pcntl_signal(SIGTERM, function ($signo) {
            echo 'SIGTERM ('.$signo.'): Cleaning up' . PHP_EOL . PHP_EOL;
            // Clean up ...
            exit(0);
        });

        pcntl_signal(SIGHUP, function ($signo) {
            echo 'SIGHUP ('.$signo.'): ...' . PHP_EOL;
            // Reload configurations here
        });

        pcntl_signal(SIGUSR1, function ($signo) {
            echo 'SIGUSR1 ('.$signo.'):  ...' . PHP_EOL;
            // Handle custom signal
        });

        while (true) {
            pcntl_signal_dispatch();
            // TODO: Your service logic goes here...
            // TODO: Detect if your service needs to do something
            // TODO: Exec in seperate process to prevent blocking main loop and finally exit(0)
        }
    }

    /**
     * @throws \Exception
     */
    protected function fork()
    {
        $pid = pcntl_fork();

        if ($pid < 0) {
            throw new \Exception('Forking the currently running process failed', 1);
        }

        if ($pid > 0) {
            // If we are here, we have successfully spawned a child process.
            // The parent process is not needed any more (exit).
            echo 'Parent ' . getmypid() . ': Exiting' . PHP_EOL;
            exit(0);
        }

        echo     'Child  ' . getmypid() . ': Active' . PHP_EOL;
    }

    /**
     * We want to be the process session/group leader. Further processes
     * created by the daemon will be a subprocess of this one here and
     * therefore depend on our new main process created via $this->>fork().
     *
     * @throws \Exception
     */
    protected function lead()
    {
        $sid = posix_setsid();
        if ($sid < 0) {
            throw new \Exception('Making the current process a session leader failed', 2);
        }
    }

    protected function setPid()
    {
        if ($this->container['pid']===false) {
            throw new \Exception('Can not access PID file');
        }

        $lock = flock($this->container['pid'], LOCK_EX | LOCK_NB, $block);

        if (!$lock) {
            throw new \Exception('Could not acquire lock on PID file');
        }

        if ($block) {
            throw new \Exception('Another instance is already running; Terminating');
        }

        ftruncate($this->container['pid'], 0);
        fwrite($this->container['pid'], getmypid() . PHP_EOL);
    }
}
