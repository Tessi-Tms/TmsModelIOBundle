<?php

/**
 * @author Jean-Philippe Chateau <jp.chateau@trepia.fr>
 */

namespace Tms\Bundle\ModelIOBundle\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use Tms\Bundle\ModelIOBundle\Serializer\ImportExportSerializer;

class ImportExportHandler
{
    private $importExportSerializer;
    private $objectManager;
    private $namespace;
    private $fields;

    /**
     * Constructor
     *
     * @param ImportExportSerializer $importExportSerializer
     * @param ObjectManager          $objectManager
     * @param string                 $namespace
     * @param array                  $fields
     */
    public function __construct(ImportExportSerializer $importExportSerializer, ObjectManager $objectManager, $namespace, array $fields)
    {
        $this->importExportSerializer = $importExportSerializer;
        $this->objectManager          = $objectManager;
        $this->namespace              = $namespace;
        $this->fields                 = $fields;
    }

    /**
     * Export a given object
     *
     * @param Object $object
     * @return array
     */
    public function exportObject($object)
    {
        $classMetadata = $this->getClassMetadata();
        $fieldMappings = $this->objectManager()->getClassMetadata($this->namespace);

        $serializedEntity = array();
        foreach ($fieldMappings as $key => $fieldMapping) {
            if (!in_array($key, $this->fields)) {
                continue;
            }

            $serializedEntity[$key] = $classMetadata->getFieldValue($entity, $key);
        }

        return $serializedEntity;
    }
}
