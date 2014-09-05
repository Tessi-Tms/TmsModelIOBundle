<?php

/**
 * @author: Julien ANDRE <j.andre@trepia.fr>
 */

namespace Tms\Bundle\ModelIOBundle\Import;

use JMS\Serializer\SerializerInterface;
use JMS\Serializer\DeserializationContext;
use Doctrine\ORM\EntityManager;

class ImporterEntity extends Importer
{
    protected $entityManager;

    /**
     * Constructor
     */
    public function __construct(SerializerInterface $serializer, EntityManager $entityManager)
    {
        parent::__construct($serializer);
        $this->entityManager = $entityManager;
    }

    /**
     * Get the entity manager
     *
     * @return EntityManager the entity manager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * Persist given entity
     *
     * @return Entity
     */
    public function persist(& $object)
    {
        $this->getEntityManager()->persist($object);

        return $object;
    }

    /**
     * Flush
     */
    public function flush()
    {
        $this->getEntityManager()->flush();
    }
}