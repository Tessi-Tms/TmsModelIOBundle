<?php

/**
 * @author: Julien ANDRE <j.andre@trepia.fr>
 */

namespace Tms\Bundle\ModelIOBundle\Import;

use JMS\Serializer\SerializerInterface;
use JMS\Serializer\DeserializationContext;
use Doctrine\ORM\EntityManager;

class EntityImporter extends AbstractImporter
{
    protected $entityManager;

    /**
     * Constructor
     *
     * @param SerializerInterface $serializer
     * @param EntityManager       $entityManager
     */
    public function __construct(SerializerInterface $serializer, EntityManager $entityManager)
    {
        parent::__construct($serializer);
        $this->entityManager = $entityManager;
    }

    /**
     * Get the entity manager
     *
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function persist(& $object)
    {
        $this->getEntityManager()->persist($object);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        $this->getEntityManager()->flush();
    }
}