<?php

/**
 * @author Jean-Philippe Chateau <jp.chateau@trepia.fr>
 */

namespace Tms\Bundle\ModelIOBundle\Handler;

class ImportExport
{
    private $handlers;

    public function __construct()
    {
        $this->handlers = array();
    }

    public function addHandler(ImportExportHandler $handler)
    {
        array_push($this->handlers, $handler);
    }

    public function guessHandler($namespace, $mode)
    {
        return;
    }

    /**
     * Export
     *
     * @param array $objects
     * @return string
     */
    public function export(array $objects)
    {
        $serializedObjects = array();
        foreach ($objects as $object) {
            array_push($serializedObjects, $this->guessHandler()->exportObject($object));
        }

        return $this->importExportSerializer->serialize($serializedObjects);
    }
}