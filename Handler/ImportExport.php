<?php

/**
 * @author Jean-Philippe Chateau <jp.chateau@trepia.fr>
 */

namespace Tms\Bundle\ModelIOBundle\Handler;

use Tms\Bundle\ModelIOBundle\Serializer\ImportExportSerializer;
use Tms\Bundle\ModelIOBundle\Exception\HandlerNotFoundException;
use Tms\Bundle\ModelIOBundle\Exception\HandlerClassNameNotFoundException;

class ImportExport
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
    public function guessHandler($className, $mode)
    {
        $index = $this->buildHandlerIndex($className, $mode);
        if (!isset($this->handlers[$index])) {
            throw new HandlerNotFoundException($className, $mode);
        }

        return $this->handlers[$index];
    }

    /**
     * Get Handler ClassName By Model
     *
     * @param string $model
     * @return string
     */
    private function getHandlerClassNameByModel($model)
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

        return $className;
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
            array_push($objectsToSerialize, $this->guessHandler(get_class($object), $mode)->exportObject($object));
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
        $className = $this->getHandlerClassNameByModel($model);
        foreach ($deserializedObjects as $deserializedObject) {
            array_push($objects, $this->guessHandler($className, $mode)->importObject($deserializedObject));
        }

        return $objects;
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
}