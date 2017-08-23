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
     * Main loop interval in seconds
     */
    const TICK_SECS = 1;

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
        $this->pid();

        pcntl_signal(SIGTERM, function ($signo) {
            echo 'SIGTERM ('.$signo.')' . PHP_EOL . PHP_EOL;
            // Clean up temp files, caches, etc.
            exit(0);
        });

        pcntl_signal(SIGHUP, function ($signo) {
            echo 'SIGHUP ('.$signo.')' . PHP_EOL;
            // Reload configs, reload pimple container, etc.
        });

        pcntl_signal(SIGUSR1, function ($signo) {
            echo 'SIGUSR1 ('.$signo.')' . PHP_EOL;
            // Handle custom signal
        });

        while (true) {
            time_sleep_until(microtime(true) + self::TICK_SECS);
            pcntl_signal_dispatch();
            $this->tick();
        }
    }

    /**
     * Will be executed in intervalls.
     */
    protected function tick()
    {
        // TODO: Your service logic goes here...
        // TODO: Detect if your service needs to do something
        // TODO: Exec in seperate process to prevent blocking main loop and finally exit(0)

        // $units = ['b','kb','mb','gb','tb','pb'];
        // $size = memory_get_usage(true);
        //
        // $value = @round($size/pow(1024, ($idx=floor(log($size, 1024)))), PHP_ROUND_HALF_DOWN);
        // $unit = $units[intval($idx)];
        //
        // echo 'MemoryLimit: ' . ini_get('memory_limit') . PHP_EOL;
        // echo 'MemoryUsage: ' . $value . ' ' . $unit . PHP_EOL;
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
            echo 'PID ' . getmypid() . ' exiting' . PHP_EOL;
            exit(0);
        }

        echo     'PID ' . getmypid() . ' is active' . PHP_EOL;
    }

    /**
     * We want to be the process session/group leader. Further processes
     * created by the daemon will be a subprocess of this one here and
     * therefore depend on our new main process created via $this->fork().
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

    /**e
     * Write a PID file and lock it
     *
     * @throws \Exception
     */
    protected function pid()
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
