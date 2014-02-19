<?php

/**
 * @author Jean-Philippe Chateau <jp.chateau@trepia.fr>
 */

namespace Tms\Bundle\ModelIOBundle\Handler;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileHandler
{
    private static $importDirectory;
    private static $allowedExtensions = array('json');

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
     * @return string|boolean
     */
    public function fileImport(UploadedFile $file)
    {
        if (!self::isValidFile($file)) {
            return false;
        }
        if (!is_dir(self::$importDirectory)) {
            mkdir(self::$importDirectory, 0755);
        }

        $filename = sprintf('%s.%s',
            md5($file->getClientOriginalName()),
            pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION)
        );
        $file->move(self::$importDirectory, $filename);
        $filePath = self::$importDirectory . $filename;

        $handler = fopen($filePath, 'r');
        $content = fread($handler, filesize($filePath));
        fclose($handler);
        unlink($filePath);

        if (!self::isValidJson($content)) {
            return false;
        }

        return $content;
    }

    /**
     * Check if a file is valid
     * It checks the extension
     *
     * @param UploadedFile $file
     * @return boolean
     */
    protected static function isValidFile(UploadedFile $file)
    {
        return in_array(pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION), self::$allowedExtensions);
    }

    /**
     * Is valid json
     *
     * @param string $toCheck
     * @return boolean
     */
    protected static function isValidJson($toCheck)
    {
        return !empty($toCheck) && is_string($toCheck) && is_array(json_decode($toCheck, true)) && json_last_error() == 0;
    }
}