<?php

/**
 * @author Jean-Philippe Chateau <jp.chateau@trepia.fr>
 */

namespace Tms\Bundle\ModelIOBundle\Handler;

//use Doctrine\Common\Persistence\ObjectManager;

class ImportExportHandler
{
    private $objectManager;
    private $className;
    private $mode;
    private $fields;

    /**
     * Constructor
     *
     * @param Object $objectManager
     * @param string $className
     * @param string $mode
     * @param array  $fields
     */
    public function __construct($objectManager, $className, $mode, array $fields)
    {
        $this->objectManager = $objectManager->getManager();
        $this->className     = $className;
        $this->mode          = $mode;
        $this->fields        = $fields;
    }

    /**
     * Get ClassName
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * Get Mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Get Fields
     *
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Export a given object
     *
     * @param Object $object
     * @return array
     */
    public function exportObject($object)
    {
        $classMetadata = $this->objectManager->getClassMetadata($this->className);
        $fieldMappings = $classMetadata->fieldMappings;

        $exportedObject = array();
        foreach ($fieldMappings as $key => $fieldMapping) {
            if (!in_array($key, $this->fields)) {
                continue;
            }
            $exportedObject[$key] = $classMetadata->getFieldValue($object, $key);
        }

        return $exportedObject;
    }
}
