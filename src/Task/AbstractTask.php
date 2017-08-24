<?php

namespace PhpDaemon\Task;

use Monolog\Logger;
use PhpDaemon\Exception\ForkException;

/**
 * Class AbstractTask
 * @package PhpDaemon\Task
 */
abstract class AbstractTask
{
    /**
     * @var Logger
     */
    protected $log;

    /**
     * @param Logger $logger
     */
    public function __construct($logger)
    {
        $this->log = $logger;
    }

    /**
     * @return bool
     * @throws ForkException
     */
    protected function split()
    {
        $pid = pcntl_fork();

        if ($pid < 0) {
            throw new ForkException();
        }

        if ($pid > 0) {
            $this->log->debug('Returning parent process');
            return true;
        }

        $this->log->debug('Task process active ');

        return false;
    }
}
