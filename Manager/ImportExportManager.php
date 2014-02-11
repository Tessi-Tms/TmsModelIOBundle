<?php

/**
 * @author Jean-Philippe Chateau <jp.chateau@trepia.fr>
 */

namespace Tms\Bundle\ModelIOBundle\Manager;

use Tms\Bundle\ModelIOBundle\Handler\ImportExportHandler;
use Tms\Bundle\ModelIOBundle\Serializer\ImportExportSerializer;
use Tms\Bundle\ModelIOBundle\Exception\HandlerNotFoundException;
use Tms\Bundle\ModelIOBundle\Exception\HandlerClassNameNotFoundException;

class ImportExportManager
{
    private $importExportSerializer;
    private $handlers;

    /**
     * Constructor
     *
     * @param ImportExportSerializer $importExportSerializer
     */
    public function __construct(ImportExportSerializer $importExportSerializer)
    {
        $this->importExportSerializer = $importExportSerializer;
        $this->handlers = array();
    }

    /**
     * Add a handler
     *
     * @param ImportExportHandler $handler
     */
    public function addHandler(ImportExportHandler $handler)
    {
        $index = $this->buildHandlerIndex($handler->getClassName(), $handler->getMode());
        $this->handlers[$index] = $handler;
    }

    /**
     * Guess a handler from given className and mode
     *
     * @param string $className
     * @param string $mode
     * @throws HandlerNotFoundException
     * @return ImportExportHandler
     */
    public function guessHandler($identifier, $mode)
    {
        $handler = null;
        try {
            $handler = $this->getHandlerByModelAndMode($identifier, $mode);
        } catch (\Exception $exception) {
            try {
                $handler = $this->guessHandlerByClassNameAndMode($identifier, $mode);
            } catch (\Exception $exception) {
                throw new \Exception($exception->getMessage());
            }
        }

        return $handler;
    }

    /**
     * Export
     *
     * @param array|Collection  $objects
     * @param string $mode
     * @return string
     */
    public function export($objects, $mode)
    {
        $objectsToSerialize = $this->exportNoSerialization($objects, $mode);

        return $this->importExportSerializer->serialize($objectsToSerialize);
    }

    /**
     * Export
     *
     * @param array|Collection  $objects
     * @param string $mode
     * @return array
     */
    public function exportNoSerialization($objects, $mode)
    {
        $objectsToSerialize = array();
        foreach ($objects as $object) {
            $class = $this->getEntityReflectionClass($object);
            array_push($objectsToSerialize, $this->guessHandler($class->getName(), $mode)->exportObject($object));
        }

        if (count($objectsToSerialize) === 1) {
            return $objectsToSerialize[0];
        }

        return $objectsToSerialize;
    }

    /**
     * Import
     *
     * @param string $content
     * @param string $model
     * @param string $mode
     * @return array
     */
    public function import($content, $model, $mode)
    {
        $objects = array();
        $deserializedObjects = $this->importExportSerializer->deserialize($content);
        foreach ($deserializedObjects as $deserializedObject) {
            array_push($objects, $this->guessHandler($model, $mode)->importObject($deserializedObject));
        }

        return $objects;
    }

    /**
     * Import No Deserialization
     *
     * @param string $content
     * @param string $model
     * @param string $mode
     * @return array
     */
    public function importNoDeserialization($content, $model, $mode)
    {
        if (is_array($content)) {
            $objects = array();
            foreach ($content as $objectToImport) {
                array_push($objects, $this->guessHandler($model, $mode)->importObject($objectToImport));
            }

            return $objects;
        } else {
            return $this->guessHandler($model, $mode)->importObject($content);
        }
    }

    /**
     * Build an identifier based on the className and the mode of the Handler
     *
     * @param string $className
     * @param string $mode
     * @return string
     */
    private function buildHandlerIndex($className, $mode)
    {
        return md5($className . $mode);
    }


    /**
     * Guess a handler from given className and mode
     *
     * @param string $className
     * @param string $mode
     * @throws HandlerNotFoundException
     * @return ImportExportHandler
     */
    private function guessHandlerByClassNameAndMode($className, $mode)
    {
        $index = $this->buildHandlerIndex($className, $mode);
        if (!isset($this->handlers[$index])) {
            throw new HandlerNotFoundException($className, $mode);
        }

        return $this->handlers[$index];
    }

    /**
     * Get Handler ClassName By Model and mode
     *
     * @param string $model
     * @param string $mode
     * @throws HandlerClassNameNotFoundException
     * @return ImportExportHandler
     */
    private function getHandlerByModelAndMode($model, $mode)
    {
        $className = null;
        foreach ($this->handlers as $handler) {
            if ($handler->getModelName() === $model) {
                $className = $handler->getClassName();
            }
        }

        if (!$className) {
            throw new HandlerClassNameNotFoundException($model);
        }

        return $this->guessHandlerByClassNameAndMode($className, $mode);
    }

    /**
     * Is a proxy class
     *
     * @param ReflectionClass $reflection
     * @return boolean
     */
    public static function isProxyClass(\ReflectionClass $reflection)
    {
        return in_array('Doctrine\ORM\Proxy\Proxy', array_keys($reflection->getInterfaces()));
    }

    /**
     * getEntityReflectionClass
     *
     * @param Object $entity
     * @return ReflectionClass
     */
    public function getEntityReflectionClass($entity)
    {
        $reflection = new \ReflectionClass($entity);
        if (self::isProxyClass($reflection) && $reflection->getParentClass()) {
            return $reflection->getParentClass();
        }

        return $reflection;
    }

    /**
     * Returns given word as CamelCased
     *
     * Converts a word like "send_email" to "SendEmail". It
     * will remove non alphanumeric character from the word, so
     * "who's online" will be converted to "WhoSOnline"
     *
     * @access public
     * @static
     * @see variablize
     * @param    string    $word    Word to convert to camel case
     * @return string UpperCamelCasedWord
     */
    public static function camelize($word)
    {
        return str_replace(' ', '', ucwords(preg_replace('/[^A-Z^a-z^0-9]+/', ' ', $word)));
    }
}