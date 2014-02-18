<?php

/**
 * @author Jean-Philippe Chateau <jp.chateau@trepia.fr>
 */

namespace Tms\Bundle\ModelIOBundle\Exception;

class MissingImportFieldException extends \Exception
{
    public function __construct($key, $className)
    {
        parent::__construct(sprintf('Key %s for class %s is missing.', $key, $className));
    }
}
