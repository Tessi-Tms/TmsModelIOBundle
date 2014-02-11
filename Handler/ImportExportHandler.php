<?php

/**
 * @author Jean-Philippe Chateau <jp.chateau@trepia.fr>
 */

namespace Tms\Bundle\ModelIOBundle\Handler;

use Tms\Bundle\ModelIOBundle\Manager\ImportExportManager;
//use Doctrine\Common\Persistence\ObjectManager;

class ImportExportHandler
{
    private $objectManager;
    private $className;
    private $modelName;
    private $mode;
    private $fields;
    private $repository;
    private $importExportManager;

    /**
     * Constructor
     *
     * @param Object              $objectManager
     * @param string              $className
     * @param string              $modelName
     * @param string              $mode
     * @param array               $fields
     * @param Object              $repository
     * @param ImportExportManager $importExportManager
     */
    public function __construct($objectManager, $className, $modelName, $mode, array $fields, $repository, ImportExportManager $importExportManager)
    {
        $this->objectManager       = $objectManager->getManager();
        $this->className           = $className;
        $this->modelName           = $modelName;
        $this->mode                = $mode;
        $this->fields              = $this->checkAndPrepareFields($fields);
        $this->repository          = $repository;
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
     * Get Repository
     *
     * @return Object
     */
    public function getRepository()
    {
        return $this->repository;
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

        //@todo participation_full: export associations

        foreach ($associationMappings as $key => $properties) {
            if (!in_array($key, array_keys($this->fields))) {
                continue;
            }
            if (!$properties['isOwningSide'] || $properties['isOwningSide'] && !isset($properties['joinColumns'] )) {
                continue;
            }
            $value = $classMetadata->getFieldValue($object, $key);
            if ($value) {
                $exportedObject[$key] = $this->importExportManager->exportNoSerialization(array($value), $this->fields[$key] ? $this->fields[$key] : 'default');
            } else {
                $exportedObject[$key] = null;
            }
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
                $importedObject = $this->getRepository()->find($object->$key);

                return $importedObject;
            }

            $classMetadata->setFieldValue($importedObject, $key, $object->$key);
        }

        $associationMappings = $classMetadata->associationMappings;
        foreach ($associationMappings as $key => $properties) {
            if (!in_array($key, array_keys($this->fields))) {
                continue;
            }

            if ($object->$key) {
                $classMetadata->setFieldValue($importedObject, $key, $this->importExportManager->importNoDeserialization($object->$key, $key, $this->fields[$key] ? $this->fields[$key] : 'default'));
            } else {
                $classMetadata->setFieldValue($importedObject, $key, null);
            }
        }

        var_dump($importedObject);

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
