<?php

/**
 * @author Jean-Philippe Chateau <jp.chateau@trepia.fr>
 */

namespace Tms\Bundle\ModelIOBundle\Serializer;

class ImportExportSerializer
{
    /**
     * Serialize
     *
     * @param mixed $data
     */
    public function serialize($data)
    {
        return json_encode($data);
    }

    /**
     * Deserialize
     *
     * @param mixed $data
     */
    public function deserialize($data)
    {
        return json_decode($data);
    }
}