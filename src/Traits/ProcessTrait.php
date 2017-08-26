<?php

namespace PhpDaemon\Traits;

use PhpDaemon\Exception;

/**
 * Trait ProcessTrait
 * @package PhpDaemon\Traits
 */
trait ProcessTrait
{
    /**
     * Forks current process and exits the parent process.
     * Only the child process will continue to live.
     *
     * @throws Exception\ForkException
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
     * Make the current process the session leader
     *
     * @throws Exception\LeadException
     */
    protected function lead()
    {
        $sid = posix_setsid();
        if ($sid < 0) {
            throw new Exception\LeadException();
        }
    }

    /**
     * Forks current process and returns TRUE on parent and FALSE on child process
     *
     * @return bool
     * @throws Exception\ForkException
     */
    protected function split()
    {
        $pid = pcntl_fork();

        if ($pid < 0) {
            throw new Exception\ForkException();
        }

        if ($pid > 0) {
            return true;
        }

        return false;
    }

    /**
     * @param resource $res
     * @throws Exception\BlockException
     * @throws Exception\FileAccessException
     * @throws Exception\LockException
     */
    protected function pid($res)
    {
        if ($res===false) {
            throw new Exception\FileAccessException();
        }

        $lock = flock($res, LOCK_EX | LOCK_NB, $block);

        if (!$lock) {
            throw new Exception\LockException();
        }

        if ($block) {
            throw new Exception\BlockException();
        }

        ftruncate($res, 0);
        fwrite($res, getmypid() . PHP_EOL);
    }
}
