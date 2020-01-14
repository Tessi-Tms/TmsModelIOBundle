<?php

/**
 * @author Jean-Philippe Chateau <jp.chateau@trepia.fr>
 */

namespace Tms\Bundle\ModelIOBundle\Handler;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tms\Bundle\ModelIOBundle\Exception\BadFileExtensionException;
use Tms\Bundle\ModelIOBundle\Exception\UnvalidContentException;

class FileHandler
{
    /**
     * Expected file type.
     *
     * @var array
     */
    private static $allowedMimeTypes = array(
        'application/json',
    );

    /**
     * Zipped file type.
     *
     * @var array
     */
    private static $zippedMimeTypes = array(
        'application/json+gzip',
        'application/gzip',
        'application/x-gzip',
    );

    /**
     * Read the content of the uploaded file
     *
     * @param UploadedFile $file
     * @return string|boolean
     */
    public function fileImport(UploadedFile $file)
    {
        // Check the file type
        if (!in_array($file->getMimeType(), array_merge(self::$allowedMimeTypes, self::$zippedMimeTypes))) {
            throw new BadFileExtensionException();
        }

        // Read the file content
        $content = '';
        if ($splFileObject = $file->openFile()) {
            while (!$splFileObject->eof()) {
                $content .= $splFileObject->fgets();
            }
        }

        // Uncompress content
        if (in_array($file->getMimeType(), self::$zippedMimeTypes)) {
            $content = gzdecode($content);
        }

        // Is the content JSON valid ?
        if (!self::isValidJson($content)) {
            throw new UnvalidContentException();
        }

        return $content;
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
