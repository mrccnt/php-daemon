<?php

namespace PhpDaemon;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger;
use PhpDaemon\Task\MyTask;
use PhpDaemon\Traits\ProcessTrait;
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
    use ProcessTrait;

    const VERSION = '1.0.0';
    const NAME = 'PHP Daemon';
    const PACKET = 'php-daemon';

    /**
     * Main loop interval in seconds as float
     */
    const TICK_SECS = 5.0;

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
        $this->pid($this->container['pid']);

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

        $this->loop();
    }

    /**
     * Main Loop
     */
    protected function loop()
    {
        /** @var Logger $log */
        $log = $this->container['logger'];
        
        while (true) {
            // Dispatch signals if any
            pcntl_signal_dispatch();

            // Our custom Task
            $this->container['mytask']->execute();

            // Slow down...
            $log->debug('Main Sleep ('.intval(self::TICK_SECS).')');
            time_sleep_until(microtime(true) + self::TICK_SECS);
            $log->debug('Main Awake');
        }
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
            return [
                'pid' => [
                    'file' => '/var/run/php-daemon.pid'
                ],
                'logger' => [
                    'name' => 'php-daemon',
                    'ident' => 'php-daemon',
                    'facility' => LOG_SYSLOG,
                    'level' => Logger::DEBUG,
                    'bubble' => true,
                    'logopts' => LOG_PID,
                ],
            ];
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
            $cfg = $container['settings']['logger'];
            $handler = new SyslogHandler(
                $cfg['ident'],
                $cfg['facility'],
                $cfg['level'],
                $cfg['bubble'],
                $cfg['logopts']
            );
            $handler->setFormatter(new LineFormatter("%level_name%: %message% %extra%"));
            $mono = new Logger($cfg['name']);
            $mono->pushHandler($handler);
            return $mono;
        };

        /**
         * @param Container $container
         * @return MyTask
         */
        $this->container['mytask'] = function ($container) {
            return new MyTask($container['logger']);
        };
    }
}
