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
     * @param array $data
     * @return string
     */
    public function serialize(array $data)
    {
        return json_encode($data);
    }

    /**
     * Deserialize
     *
     * @param string $data
     * @return array
     */
    public function deserialize($data)
    {
        return json_decode($data);
    }
}