<?php

namespace PhpDaemon\Exception;

/**
 * Class LeadException
 * @package PhpDaemon\Exception
 */
class LeadException extends \Exception
{
    /**
     * LeadException constructor.
     */
    public function __construct()
    {
        parent::__construct('Making the current process a session leader failed', 2);
    }
}
