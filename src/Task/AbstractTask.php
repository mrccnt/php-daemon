<?php

namespace PhpDaemon\Task;

use Monolog\Logger;

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
     * Execute the task
     */
    public function execute()
    {
        $this->log->crit('Task '.get_class($this).' is not configured!');
        $this->log->crit('Overwrite AbstractTask::execute() with your own logic');
        exit(0);
    }
}
