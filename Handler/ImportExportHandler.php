<?php

/**
 * @author Jean-Philippe Chateau <jp.chateau@trepia.fr>
 */

namespace Tms\Bundle\ModelIOBundle\Handler;

use Tms\Bundle\ModelIOBundle\Manager\ImportExportManager;
use Tms\Bundle\ModelIOBundle\Exception\AlreadyExistingEntityException;
use Tms\Bundle\ModelIOBundle\Exception\MissingImportFieldException;
use Tms\Bundle\ModelIOBundle\Handler\MediaHandler;
use Doctrine\ORM\Mapping\ClassMetadata;

class ImportExportHandler
{
    /**
     * Object Manager used (eg: doctrine).
     *
     * @var Object
     */
    private $objectManager;

    /**
     * Name of the class (eg: Tms\Bundle\OperationBundle\Entity\Benefit).
     *
     * @var string
     */
    private $className;

    /**
     * Name of the model (eg: benefit).
     *
     * @var string
     */
    private $modelName;

    /**
     * Defined mode (eg: simple).
     *
     * @var string
     */
    private $mode;

    /**
     * Array of fields to import/export.
     *
     * @var array
     */
    private $fields;

    /**
     * Array of aliases given to the manager.
     *
     * @var array
     */
    private $aliases;

    /**
     * All the newly created entities.
     *
     * @var array
     */
    static $importedEntities;

    /**
     * The Import/Export Manager.
     *
     * @var ImportExportManager
     */
    private $importExportManager;

    /**
     * Instance of MediaHandler.
     *
     * @var MediaHandler
     */
    private $mediaHandler;

    /**
     * Constructor.
     *
     * @param Object              $objectManager
     * @param string              $className
     * @param string              $modelName
     * @param string              $mode
     * @param array               $fields
     * @param array               $aliases
     * @param ImportExportManager $importExportManager
     * @param MediaHandler        $mediaHandler
     */
    public function __construct(
        $objectManager,
        $className,
        $modelName,
        $mode,
        array $fields,
        array $aliases,
        ImportExportManager $importExportManager,
        MediaHandler $mediaHandler = null
    ) {
        $this->objectManager = $objectManager->getManager();
        $this->className = $className;
        $this->modelName = $modelName;
        $this->mode = $mode;
        $this->fields = $this->checkAndPrepareFields($fields);
        $this->aliases = $aliases;
        $this->importExportManager = $importExportManager;
        $this->mediaHandler = $mediaHandler;

        if (!is_array(self::$importedEntities)) {
            self::$importedEntities = array();
        }
    }

    /**
     * Get ClassName.
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * Get ModelName.
     *
     * @return string
     */
    public function getModelName()
    {
        return $this->modelName;
    }

    /**
     * Get Mode.
     *
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Get Fields.
     *
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Get Aliases.
     *
     * @return array
     */
    public function getAliases()
    {
        return $this->aliases;
    }

    /**
     * Export a given object.
     *
     * @param Object $object
     *
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
                $getter = sprintf('get%s', ImportExportManager::camelize($key));
                if (!method_exists($object, $getter)) {
                    $getter = sprintf('is%s', ImportExportManager::camelize($key));
                }
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

            $exportedObject[$key] = $this->importExportManager->exportNoSerialization(
                $fieldValues,
                isset($this->fields[$key]['mode']) ? $this->fields[$key]['mode'] : 'default',
                $isCollection
            );
        }

        return $exportedObject;
    }

    /**
     * Import an object
     *
     * @param array $object
     *
     * @return Object
     */
    public function importObject($object)
    {
        // Verify the data integrity
        $this->checkData($object);

        // Retrieve an existing entity
        $entity = $this->retrieveEntity($object);
        if (! $entity instanceof $this->className) {
            // Create a new entity
            $entity = new $this->className();
        }

        // Check the object unicity
        $this->checkUnicity($object);

        // Extract the entity metadatas
        $classMetadata = $this->objectManager->getClassMetadata($this->className);

        // Save the new entity
        $this->keep($object, $entity);

        // Set the value of simple fields
        foreach ($classMetadata->fieldMappings as $key => $fieldMapping) {
            if (!property_exists($object, $key)) {
                continue;
            }
            $classMetadata->setFieldValue($entity, $key, $this->transformData($object->$key, $fieldMapping['type']));
        }

        // Set the value of associated fields
        foreach ($classMetadata->associationMappings as $key => $fieldMapping) {
            if (!property_exists($object, $key)) {
                continue;
            }

            if ($object->$key) {
                // Create the associated entities
                $associatedEntities = $this->importExportManager->importNoDeserialization(
                    $object->$key,
                    isset($this->fields[$key]['model']) ? $this->fields[$key]['model'] : $key,
                    isset($this->fields[$key]['mode']) ? $this->fields[$key]['mode'] : 'default'
                );

                // Associate the new entities
                $classMetadata->setFieldValue($entity, $key, $associatedEntities);

                // Update associated entities sides
                if (!is_array($associatedEntities)) {
                    $associatedEntities = array($associatedEntities);
                }

                // Handle OneToOne and OneToMany associations
                if (isset($fieldMapping['mappedBy'])) {
                    foreach ($associatedEntities as $associatedEntity) {
                        $this
                            ->objectManager
                            ->getClassMetadata(get_class($associatedEntity))
                            ->setFieldValue($associatedEntity, $fieldMapping['mappedBy'], $entity);
                    }
                } else {
                    dump($fieldMapping);die;
                }
            } else {
                $classMetadata->setFieldValue($entity, $key, is_array($object->$key) ? array() : null);
            }
        }

        // Handle the specific case of Media
        if (null !== $this->mediaHandler && $this->mediaHandler->isMedia($entity)) {
            return $this->mediaHandler->importMedia($entity);
        }

        // Return the entity
        return $entity;
    }



    /**
     * Get the unique constraints of an object.
     *
     * @param mixed $object The object
     *
     * @return array
     */
    protected function getUniqueConstraints($object)
    {
        // Extract the entity metadatas
        $classMetadata = $this->objectManager->getClassMetadata($this->className);

        $uniqueConstraints = array();
        if (isset($classMetadata->table['uniqueConstraints'])) {

            // Parse the unique constraints in table metadata
            foreach ($classMetadata->table['uniqueConstraints'] as $key => $value) {
                $fields = array();
                if (isset($value['columns'])) {

                    // Get the constraint fields and values
                    foreach ($value['columns'] as $column) {
                        // Ignore null values
                        if (!isset($object->$column)) {
                            $fields = array();

                            break;
                        }

                        // Set the constraint field value
                        $fields[$column] = $object->$column;
                    }

                    // Ignore constraints without fields values
                    if (empty($fields)) {
                        continue;
                    }

                    $uniqueConstraints[$key] = $fields;
                }
            }
        }

        return $uniqueConstraints;
    }

    /**
     * Generate the hashes of the entities for each unique constraints.
     *
     * @param mixed $object The object to import.
     *
     * @return array
     */
    public function generateEntityHashes($object)
    {
        $hashes = array();
        foreach ($this->getUniqueConstraints($object) as $key => $value) {
            $hashes[] = md5(serialize(array_merge($value, array(
                'className' => $this->className,
                'uniqueKey' => $key,
            ))));
        }

        return $hashes;
    }

    /**
     * Retrieve an existing entity or a newly created one.
     * Will return null if no entity are found.
     *
     * @param mixed $object The object to retrieve
     *
     * @return object|null
     */
    protected function retrieveEntity($object)
    {
        // Search in already created/funded entities
        foreach ($this->generateEntityHashes($object) as $hash) {
            if (isset(self::$importedEntities[$hash])) {
                return self::$importedEntities[$hash];
            }
        }

        // Extract the entity metadatas
        $classMetadata = $this->objectManager->getClassMetadata($this->className);

        // Get the object identifiers
        $identifiers = array();
        foreach ($classMetadata->identifier as $key => $value) {
            if (isset($object->$value)) {
                $identifiers[$value] = $object->$value;

                // Ignore foreign ids
                if ($object->$value instanceof \stdClass) {
                    $identifiers = array();

                    break;
                }
            }
        }

        // Search the entity in the database by its identifiers
        if (!empty($identifiers)) {
            $entity = $this->objectManager->getRepository($this->className)->findOneBy($identifiers);

            // Return the existing entity
            if ($entity instanceof $this->className) {
                return $entity;
            }
        }

        return null;
    }

    /**
     * Check the unicity of the object to import.
     *
     * @param mixed $object The object to import
     *
     * @return boolean
     *
     * @throws AlreadyExistingEntityException
     */
    protected function checkUnicity($object)
    {
        foreach ($this->getUniqueConstraints($object) as $fields) {
            $parameters = array();
            foreach ($fields as $key => $value) {
                // Ignore complexe values
                if ($value instanceof \stdClass) {
                    $parameters = array();

                    break;
                }

                $parameters[$key] = $value;
            }
            if (!empty($parameters)) {
                $entity = $this->objectManager->getRepository($this->className)->findOneBy($fields);

                // Return the existing entity
                if ($entity instanceof $this->className) {
                    throw new AlreadyExistingEntityException($this->className, $fields);
                }
            }
        }

        return true;
    }

    /**
     * Check the object data.
     *
     * @param mixed $object The object to import
     *
     * @return boolean
     *
     * @throws MissingImportFieldException
     */
    protected function checkData($object)
    {
        // Check missing fields data
        foreach ($this->fields as $key => $value) {
            if (!property_exists($object, $key)) {
                throw new MissingImportFieldException($key);
            }
        }

        // Check excess data
        foreach (get_object_vars($object) as $key => $value) {
            if (!in_array($key, array_keys($this->fields))) {
                throw new MissingImportFieldException($key);
            }
        }

        return true;
    }

    /**
     * Keep the entity in memory to avoid multiple loading.
     *
     * @param mixed $object The initial object
     * @param mixed $entity The created entity
     *
     * @return mixed
     */
    protected function keep($object, $entity)
    {
        foreach ($this->generateEntityHashes($object) as $hash) {
            self::$importedEntities[$hash] = $entity;
        }
    }

    /**
     * Keep the entity in memory to avoid multiple loading and return it.
     *
     * @param mixed $object The initial object
     * @param mixed $entity The created entity
     *
     * @return mixed
     */
    protected function keepAndReturn($object, $entity)
    {
        $this->keep($object, $entity);

        return $entity;
    }

    /**
     * Take the fields defined by the model mode and returns an array indexed by the model name
     * Example :
     * array (size=6)
     *    'onlineEnabled' => null
     *    'offlineEnabled' => null
     *    'previewBallotBeforeDownloadEnabled' => null
     *    'eligibilities' => array(
     *          'mode' => string 'simple' (length=6)
     *     )
     *    'steps' => array(
     *          'mode' => string 'simple' (length=6)
     *     )
     *    'benefits' => array(
     *          'mode' => string 'simple' (length=6)
     *     )
     *
     * @param array $fields
     *
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
            if (is_array($field[key($field)])) {
                $preparedFields[key($field)] = $field[key($field)];
            } else {
                $preparedFields[key($field)] = null;
            }
        }

        return $preparedFields;
    }

    /**
     * Transform data
     *
     * @param mixed $data the data to transformed
     *
     * @return mixed the data transformed
     */
    private function transformData($data, $type)
    {
        // Transform stdClass to array
        if ($data instanceof \stdClass) {
           $data = (array) $data;
        }

        // Transform to DateTime
        if ($type === 'datetime' && $data !== null && isset($data['date'])) {
            $data = new \DateTime($data['date'], new \DateTimeZone($data['timezone']));
        }

        return $data;
    }
}
