<?php

namespace PhpDaemon\Exception;

/**
 * Class FileAccessException
 * @package PhpDaemon\Exception
 */
class FileAccessException extends \Exception
{
    /**
     * @param string $file
     */
    public function __construct($file = '')
    {
        parent::__construct(trim('Can not access file ' . $file), 3);
    }
}
