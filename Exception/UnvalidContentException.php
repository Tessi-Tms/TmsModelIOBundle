<?php

/**
 * @author Jean-Philippe Chateau <jp.chateau@trepia.fr>
 */

namespace Tms\Bundle\ModelIOBundle\Exception;

class UnvalidContentException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Unvalid content detected');
    }
}
