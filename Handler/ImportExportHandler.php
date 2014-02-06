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
    private $importExport;

    /**
     * Constructor
     *
     * @param Object       $objectManager
     * @param string       $className
     * @param string       $mode
     * @param array        $fields
     * @param ImportExport $importExport
     */
    public function __construct($objectManager, $className, $mode, array $fields, ImportExport $importExport)
    {
        $this->objectManager = $objectManager->getManager();
        $this->className     = $className;
        $this->mode          = $mode;
        $this->fields        = $this->checkAndPrepareFields($fields);
        $this->importExport  = $importExport;
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
        $exportedObject = array();
        $classMetadata = $this->objectManager->getClassMetadata($this->className);
        $fieldMappings = $classMetadata->fieldMappings;

        foreach ($fieldMappings as $key => $fieldMapping) {
            if (!in_array($key, array_keys($this->fields))) {
                continue;
            }
            $exportedObject[$key] = $classMetadata->getFieldValue($object, $key);
        }

        $associationMappings = $classMetadata->associationMappings;
        foreach ($associationMappings as $key => $associationMapping) {

            if (!in_array($key, array_keys($this->fields)) || !$this->fields[$key]) {
                continue;
            }

            $exportedField = $this->importExport->export($classMetadata->getFieldValue($object, $key), $this->fields[$key]);
            $exportedObject[$key] = $exportedField;
        }

        return $exportedObject;
    }

    /**
     * Take the fields defined by the model mode and returns an array indexed by the model name
     * Example :
     * array (size=6)
     *    'onlineEnabled' => null
     *    'offlineEnabled' => null
     *    'previewBallotBeforeDownloadEnabled' => null
     *    'eligibilities' => string 'simple' (length=6)
     *    'steps' => string 'simple' (length=6)
     *    'benefits' => string 'simple' (length=6)
     *
     * @param array $fields
     * @return array
     */
    private function checkAndPrepareFields(array $fields)
    {
        $preparedFields = array();
        foreach ($fields as $key => $field) {
            if (!is_array($field)) {
                $preparedFields[$field] = null;
                continue;
            }

            if (is_array($field[key($field)]) && isset($field[key($field)]['mode'])) {
                $preparedFields[key($field)] = $field[key($field)]['mode'];
            } else {
                $preparedFields[key($field)] = null;
            }
        }

        return $preparedFields;
    }
}
