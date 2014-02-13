<?php

/**
 * @author Jean-Philippe Chateau <jp.chateau@trepia.fr>
 */

namespace Tms\Bundle\ModelIOBundle\Handler;

use Tms\Bundle\ModelIOBundle\Manager\ImportExportManager;

class ImportExportHandler
{
    private $objectManager;
    private $className;
    private $modelName;
    private $mode;
    private $fields;
    private $aliases;
    private $importExportManager;

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

            //@todo set to an empty array if it is a collection and there is no data to return
            $values = null;
            $value = $classMetadata->getFieldValue($object, $key);

            if (!$value) {
                continue;
            }

            $collection = true;
            if (ImportExportManager::isCollectionClass(new \ReflectionClass($value))) {
                $getter = 'get' . ImportExportManager::camelize($key);
                $collection = $object->$getter();
                $values = array();
                foreach ($collection as $collectionData) {
                    array_push($values, $collectionData);
                }
            } else {
                $values = array($value);
                $collection = false;
            }

            $exportedObject[$key] = $this->importExportManager->exportNoSerialization($values, $this->fields[$key] ? $this->fields[$key] : 'default', $collection);
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

            if ($key === 'id') {
                return $this->objectManager->getRepository($this->className)->find($object->$key);
            }

            $classMetadata->setFieldValue($importedObject, $key, $object->$key);
        }

        $associationMappings = $classMetadata->associationMappings;
        foreach ($associationMappings as $key => $properties) {
            if (!in_array($key, array_keys($this->fields))) {
                continue;
            }

            if (isset($object->$key) && $object->$key) {
                $classMetadata->setFieldValue($importedObject, $key, $this->importExportManager->importNoDeserialization($object->$key, $key, $this->fields[$key] ? $this->fields[$key] : 'default'));
            } else {
                $classMetadata->setFieldValue($importedObject, $key, null);
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

        if ('default' === $this->mode) {
            $classMetadata = $this->objectManager->getClassMetadata($this->className);
            $fieldMappings = $classMetadata->fieldMappings;
            foreach ($fieldMappings as $key => $fieldMapping) {
                $preparedFields[$key] = null;
            }

            return $preparedFields;
        }

        foreach ($fields as $key => $field) {
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
