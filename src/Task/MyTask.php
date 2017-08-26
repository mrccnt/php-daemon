<?php

namespace PhpDaemon\Task;

use PhpDaemon\Traits\ProcessTrait;

/**
 * Class MyTask
 * @package PhpDaemon\Task
 */
class MyTask extends AbstractTask
{
    use ProcessTrait;

    /**
     * Execute the task
     */
    public function execute()
    {

        if ($this->split()) {
            // If split() returned true, then thats the parent process.
            // Send the parent process back to main loop. From here
            // on the subprocess will handle everything else and
            // exit if finished
            return;
        }

        // This subprocess can now handle its tasks on its own,
        // while the parent process is handling its main loop

        // TODO: Your service logic goes here...
        // TODO: Detect if your service needs to do something

        $this->log->debug('Task Sleep (2)');
        time_sleep_until(microtime(true) + 2);
        $this->log->debug('Task Awake');

        $this->log->debug('Task Exit');
        exit(0);
    }
}
