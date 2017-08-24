<?php

namespace PhpDaemon;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger;
use PhpDaemon\Exception;
use PhpDaemon\Task\MyTask;
use Pimple\Container;

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

    const VERSION = '1.0.0';
    const NAME = 'PHP Daemon';
    const PACKET = 'php-daemon';

    /**
     * Main loop interval in seconds as float
     */
    const TICK_SECS = 5.0;

    /**
     * Static path to the pid file. Maybe get path from ubuntu env somehow?
     */
    const PID_FILE = '/var/run/php-daemon.pid';

    /**
     * @var Container
     */
    protected $container;

    /**
     * Register handlers to signals and enter main loop
     */
    public function run()
    {
        $this->fork();
        $this->lead();
        $this->load();
        $this->pid();

        /** @var Logger $log */
        $log = $this->container['logger'];

        pcntl_signal(SIGTERM, function ($signo) use ($log) {
            $log->info('SIGTERM ('.$signo.')');
            // Clean up temp files, caches, etc.
            exit(0);
        });

        pcntl_signal(SIGHUP, function ($signo) use ($log) {
            $log->info('SIGHUP ('.$signo.')');
            // Reload configs, reload pimple container, etc.
        });

        pcntl_signal(SIGUSR1, function ($signo) use ($log) {
            $log->info('SIGUSR1 ('.$signo.')');
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

        /** @var MyTask $task */
        $task = $this->container['mytask'];
        $task->execute();
    }

    /**
     * @throws \Exception
     */
    protected function fork()
    {
        $pid = pcntl_fork();

        if ($pid < 0) {
            throw new Exception\ForkException();
        }

        if ($pid > 0) {
            exit(0);
        }
    }

    /**
     * @throws \Exception
     */
    protected function lead()
    {
        $sid = posix_setsid();
        if ($sid < 0) {
            throw new Exception\LeadException();
        }
    }

    /**
     * @throws \Exception
     */
    protected function pid()
    {
        if ($this->container['pid']===false) {
            throw new Exception\FileAccessException(self::PID_FILE);
        }

        $lock = flock($this->container['pid'], LOCK_EX | LOCK_NB, $block);

        if (!$lock) {
            throw new Exception\LockException(self::PID_FILE);
        }

        if ($block) {
            throw new Exception\BlockException();
        }

        ftruncate($this->container['pid'], 0);
        fwrite($this->container['pid'], getmypid() . PHP_EOL);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function load()
    {
        $this->container = new Container();

        /**
         * @param Container $container
         * @return array
         */
        $this->container['settings'] = function ($container) {
            return $this->getSettings();
        };

        /**
         * @param Container $container
         * @return bool|resource
         */
        $this->container['pid'] = function ($container) {
            return fopen($container['settings']['pid']['file'], 'c');
        };

        /**
         * @param Container $container
         * @return Logger
         */
        $this->container['logger'] = function ($container) {
            $settings = $container['settings']['logger'];
            $logger = new Logger($settings['name']);
            $syslog = new SyslogHandler($settings['ident'], LOG_SYSLOG);
            $syslog->setFormatter(new LineFormatter("%level_name%: %message% %extra%"));
            $logger->pushHandler($syslog);
            return $logger;
        };

        /**
         * @param Container $container
         * @return MyTask
         */
        $this->container['mytask'] = function ($container) {
            return new MyTask($container['logger']);
        };
    }

    /**
     * @return array
     */
    protected function getSettings()
    {
        return [
            'pid' => [
                'file' => '/var/run/php-daemon.pid'
            ],
            'logger' => [
                'name' => self::PACKET,
                'ident' => 'php-daemon',
            ],
        ];
    }
}
