<?php

/**
 * @author Jean-Philippe Chateau <jp.chateau@trepia.fr>
 */

namespace Tms\Bundle\ModelIOBundle\Exception;

class HandlerClassNameNotFoundException extends \Exception
{
    public function __construct($model)
    {
        parent::__construct(sprintf('Handler ClassName for %s model was not found.', $model));
    }
}
