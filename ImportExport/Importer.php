<?php

/**
 * @author: Julien ANDRE <j.andre@trepia.fr>
 */

namespace Tms\Bundle\ModelIOBundle\ImportExport;

use JMS\Serializer\SerializerInterface;

class Importer
{
    /**
     * JMSSerializer
     */
    protected $serializer;

    /**
     * constructor
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Testing method
     *
     * @return true if service has been insticiate
     */
    public function exists() 
    {
        return "exists";
    }

    /**
     * serializer getter
     *
     * @return JMSSerializer
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * create manageable object from raw data
     *
     * @return Entity
     */
    public function createObject($entityName, $entityData, $format)
    {
        return $this->getSerializer()->deserialize($entityData, $entityName, $format);
    }

    /**
     * persist raw data
     */
    public function import($objectManager, $entityName, $entityData, $format)
    {
        $object = $this->createObject($entityName, $entityData, $format);
        $objectManager->persist($object);
        $objectManager->flush();
    }
}