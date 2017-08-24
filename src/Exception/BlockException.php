<?php

namespace PhpDaemon\Exception;

/**
 * Class BlockException
 * @package PhpDaemon\Exception
 */
class BlockException extends \Exception
{
    /**
     * BlockException constructor.
     */
    public function __construct()
    {
        parent::__construct('Lock is blocking. Another instance is already running', 5);
    }
}
