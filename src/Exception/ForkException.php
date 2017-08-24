<?php

namespace PhpDaemon\Exception;

/**
 * Class ForkException
 * @package PhpDaemon\Exception
 */
class ForkException extends \Exception
{
    /**
     * ForkException constructor.
     */
    public function __construct()
    {
        parent::__construct('Forking the currently running process failed', 1);
    }
}
