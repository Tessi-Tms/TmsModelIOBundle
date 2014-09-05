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
    /**
     * entity manager
     */
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
     * @return Manager the entity manager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * persist given entity
     */
    protected function persist($object)
    {
        $this->getEntityManager()->persist($object);
    }

    /**
     * terminate transaction
     */
    public function flush()
    {
        $this->getEntityManager()->flush();
    }
}