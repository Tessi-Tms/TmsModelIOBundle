<?php

namespace Tms\Bundle\ModelIOBundle\Handler;

use Tms\Bundle\ModelIOBundle\Serializer\ImportExportSerializer;

class ImportExportHandler
{
    private $fileHandler;
    private $importExportSerializer;
    private $entityNamespace;

    /**
     * Constructor
     *
     * @param FileHandler $fileHandler
     * @param ImportExportSerializer $importExportSerializer
     * @param string $entityNamespace
     */
    public function __construct(FileHandler $fileHandler, ImportExportSerializer $importExportSerializer, $entityNamespace)
    {
        $this->fileHandler = $fileHandler;
        $this->importExportSerializer = $importExportSerializer;
        $this->entityNamespace = $entityNamespace;
    }

    public function import()
    {

    }

    public function export()
    {

    }
}
