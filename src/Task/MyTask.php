<?php

namespace PhpDaemon\Task;

use PhpDaemon\Exception\ForkException;

/**
 * Class MyTask
 * @package PhpDaemon\Task
 */
class MyTask extends AbstractTask
{
    /**
     * Execute the task
     */
    public function execute()
    {
        if ($this->split()) {
            return;
        }

        $this->log->debug('tick');
        time_sleep_until(microtime(true) + 1);

        $this->log->debug('Exiting Task');
        exit(0);
    }
}
