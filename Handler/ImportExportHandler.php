<?php

/**
 * @author Jean-Philippe Chateau <jp.chateau@trepia.fr>
 */

namespace Tms\Bundle\ModelIOBundle\Handler;

use Tms\Bundle\ModelIOBundle\Manager\ImportExportManager;
use Tms\Bundle\ModelIOBundle\Exception\MissingImportFieldException;

class ImportExportHandler
{
    private $objectManager;        // Object Manager used (eg: doctrine)
    private $className;            // Name of the class (eg: Tms\Bundle\OperationBundle\Entity\Benefit)
    private $modelName;            // Name of the model (eg: benefit)
    private $mode;                 // Defined mode (eg: simple)
    private $fields;               // Array of fields to import/export
    private $aliases;              // Array of aliases given to the manager
    private $importExportManager;  // The Import/Export Manager

    /**
     * Constructor
     *
     * @param Object              $objectManager
     * @param string              $className
     * @param string              $modelName
     * @param string              $mode
     * @param array               $fields
     * @param array               $aliases
     * @param ImportExportManager $importExportManager
     */
    public function __construct($objectManager, $className, $modelName, $mode, array $fields, array $aliases, ImportExportManager $importExportManager)
    {
        $this->objectManager       = $objectManager->getManager();
        $this->className           = $className;
        $this->modelName           = $modelName;
        $this->mode                = $mode;
        $this->fields              = $this->checkAndPrepareFields($fields);
        $this->aliases             = $aliases;
        $this->importExportManager = $importExportManager;
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
     * Get ModelName
     *
     * @return string
     */
    public function getModelName()
    {
        return $this->modelName;
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
     * Get Aliases
     *
     * @return array
     */
    public function getAliases()
    {
        return $this->aliases;
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

            if (ImportExportManager::isProxyClass(new \ReflectionClass($object))) {
                $getter = 'get' . ImportExportManager::camelize($key);
                $exportedObject[$key] = $object->$getter();
            } else {
                $exportedObject[$key] = $classMetadata->getFieldValue($object, $key);
            }
        }

        $associationMappings = $classMetadata->associationMappings;
        foreach ($associationMappings as $key => $properties) {
            if (!in_array($key, array_keys($this->fields))) {
                continue;
            }
            $fieldValue = $classMetadata->getFieldValue($object, $key);
            if (!$fieldValue) {
                $exportedObject[$key] = $fieldValue;
                continue;
            }

            $fieldValues = array();
            $isCollection = true;
            if (ImportExportManager::isCollectionClass(new \ReflectionClass($fieldValue))) {
                $getter = sprintf('get%s', ImportExportManager::camelize($key));
                $collectionValues = $object->$getter();
                foreach ($collectionValues as $collectionValue) {
                    array_push($fieldValues, $collectionValue);
                }
            } else {
                array_push($fieldValues, $fieldValue);
                $isCollection = false;
            }

            $exportedObject[$key] = $this->importExportManager->exportNoSerialization($fieldValues, ($this->fields[$key] ? $this->fields[$key] : 'default'), $isCollection);
        }

        return $exportedObject;
    }

    /**
     * Import an object
     *
     * @param array $object
     * @return Object
     */
    public function importObject($object)
    {
        $classMetadata = $this->objectManager->getClassMetadata($this->className);
        $importedObject = new $this->className();

        $fieldMappings = $classMetadata->fieldMappings;
        foreach ($fieldMappings as $key => $fieldMapping) {
            if (!in_array($key, array_keys($this->fields))) {
                continue;
            }
            if (!property_exists($object, $key)) {
                throw new MissingImportFieldException();
            }
            $classMetadata->setFieldValue($importedObject, $key, $object->$key);
        }

        $associationMappings = $classMetadata->associationMappings;
        foreach ($associationMappings as $key => $properties) {
            if (!in_array($key, array_keys($this->fields))) {
                continue;
            }
            if (!property_exists($object, $key)) {
                throw new MissingImportFieldException();
            }

            if ($object->$key) {
                $classMetadata->setFieldValue($importedObject, $key, $this->importExportManager->importNoDeserialization($object->$key, $key, $this->fields[$key] ? $this->fields[$key] : 'default'));
            } else {
                $emptyValue = null;
                if (is_array($object->$key)) {
                    $emptyValue = array();
                }
                $classMetadata->setFieldValue($importedObject, $key, $emptyValue);
            }
        }

        return $importedObject;
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

        // In default mode, only the fields that are not associations are set to be imported/exported
        if ('default' === $this->mode) {
            $classMetadata = $this->objectManager->getClassMetadata($this->className);
            $fieldMappings = $classMetadata->fieldMappings;
            foreach ($fieldMappings as $key => $fieldMapping) {
                $preparedFields[$key] = null;
            }

            return $preparedFields;
        }

        foreach ($fields as $key => $field) {
            // If the field is not an array, it implies that no particular mode is defined
            if (!is_array($field)) {
                $preparedFields[$field] = null;
                continue;
            }

            // Get the mode of the field if it is defined
            if (is_array($field[key($field)]) && isset($field[key($field)]['mode'])) {
                $preparedFields[key($field)] = $field[key($field)]['mode'];
            } else {
                $preparedFields[key($field)] = null;
            }
        }

        return $preparedFields;
    }
}
