<?php

/**
 * @author Jean-Philippe Chateau <jp.chateau@trepia.fr>
 */

namespace Tms\Bundle\ModelIOBundle\Exception;

class BadFileExtensionException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Bad file extension detected');
    }
}
