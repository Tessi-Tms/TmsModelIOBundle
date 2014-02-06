<?php

/**
 * @author Jean-Philippe Chateau <jp.chateau@trepia.fr>
 */

namespace Tms\Bundle\ModelIOBundle\Exception;

class HandlerNotFoundException extends \Exception
{
    public function __construct($className, $mode)
    {
        parent::__construct(sprintf('Handler for %s and %s mode was not found.', $className, $mode));
    }
}
