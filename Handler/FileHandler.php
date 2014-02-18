<?php

/**
 * @author Jean-Philippe Chateau <jp.chateau@trepia.fr>
 */

namespace Tms\Bundle\ModelIOBundle\Handler;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileHandler
{
    static private $importDirectory;

    /**
     * Constructor
     *
     * @param string $importDirectory
     */
    public function __construct($importDirectory)
    {
        self::$importDirectory = $importDirectory;
    }

    /**
     * Read the content of the uploaded file
     *
     * @param UploadedFile $file
     * @return string
     */
    public function fileImport(UploadedFile $file)
    {
        if (!is_dir(self::$importDirectory)) {
            mkdir(self::$importDirectory, 0755);
        }
        $file->move(self::$importDirectory, $file->getClientOriginalName());

        if (!$this->isValidFile($file)) {
            return false;
        }

        $extension = pathinfo(self::$importDirectory . $file->getClientOriginalName(), PATHINFO_EXTENSION);
        $handler = fopen(self::$importDirectory . $file->getClientOriginalName(), 'r');
        $content = fread($handler, filesize(self::$importDirectory . $file->getClientOriginalName()));
        fclose($handler);
        unlink(self::$importDirectory . $file->getClientOriginalName());

        return $content;
    }

    /**
     * Check if a file is valid
     *
     * @param UploadedFile $file
     * @return boolean
     */
    private function isValidFile(UploadedFile $file)
    {
        $allowedExtensions = array('json');
        $extension = pathinfo(self::$importDirectory . $file->getClientOriginalName(), PATHINFO_EXTENSION);
        if (!in_array($extension, $allowedExtensions)) {
            return false;
        }

        return true;
    }
}