<?php

/**
 * @author Jean-Philippe Chateau <jp.chateau@trepia.fr>
 */

namespace Tms\Bundle\ModelIOBundle\Exception;

class MissingImportFieldException extends \Exception
{
    public function __construct($field = null)
    {
        parent::__construct(sprintf('A missing Import field has been detected (%s)', $field));
    }
}
