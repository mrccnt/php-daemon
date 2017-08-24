<?php

namespace PhpDaemon\Exception;

/**
 * Class LockException
 * @package PhpDaemon\Exception
 */
class LockException extends \Exception
{
    /**
     * @param string $file
     */
    public function __construct($file = '')
    {
        parent::__construct(trim('Could not acquire lock on file ' . $file), 4);
    }
}
