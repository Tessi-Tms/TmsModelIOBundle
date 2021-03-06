<?php

/**
 * @author Jean-Philippe Chateau <jp.chateau@trepia.fr>
 */

namespace Tms\Bundle\ModelIOBundle\Exception;

class MissingImportFieldException extends \Exception
{
    public function __construct()
    {
        parent::__construct('A missing Import field has been detected');
    }
}
