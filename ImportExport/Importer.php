<?php

/**
 * @author: Julien ANDRE <j.andre@trepia.fr>
 */

namespace Tms\Bundle\ModelIOBundle\ImportExport;

use JMS\Serializer\SerializerInterface;
use JMS\Serializer\DeserializationContext;

class Importer
{
    const SERIALIZER_CONTEXT_GROUP = 'tms_modelio';

    protected $serializer;

    /**
     * Constructor
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Get serializer context
     *
     * @return SerializationContext
     */
    public static function getContext()
    {
        $context = DeserializationContext::create();
        $context->setGroups(array(self::SERIALIZER_CONTEXT_GROUP));

        return $context;
    }

    /**
     * Get serializer
     *
     * @return JMSSerializer
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * Populate Object
     *
     * @param string $objectClassName
     * @param string $data
     * @param string $format
     * @return object
     */
    protected function populateObject($objectClassName, $data, $format = 'json')
    {
        return $this
            ->getSerializer()
            ->deserialize(
                $data,
                $objectClassName,
                $format,
                self::getContext()
            )
        ;
    }

    /**
     * Import Object
     *
     * @param string $objectClassName
     * @param string $data
     * @param string $format
     * @return object
     */
    public function import($objectClassName, $data, $format = 'json')
    {
        $object = $this->populateObject($objectClassName, $data, $format);

        return $object;
    }
}
