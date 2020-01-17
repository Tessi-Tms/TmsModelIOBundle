<?php

namespace Tms\Bundle\ModelIOBundle\Exception;

class AlreadyExistingEntityException extends \Exception
{
    /**
     * The exception.
     *
     * @param string $className ClassName of the entity
     * @param array  $data      Fields values for the unique constraint
     */
    public function __construct($className, array $data)
    {
        // Calculate entity name
        $entityName = preg_replace('/^.*\/([^\/]+)$/', "$1", strtr($className, '\\', '/'));

        // Flatten data
        $flattenedData = '';
        foreach ($data as $key => $value) {
            $flattenedData = sprintf(
                '%s%s%s: %s',
                $flattenedData,
                $flattenedData ? ',' : '',
                $key,
                $value
            );
        }

        // Create the exception
        parent::__construct(sprintf(
            'A%s "%s" entity with the same values (%s) was already found.',
            preg_match('/^[aeiouAEIOU]/', $entityName) ? 'n' : '',
            strtolower($entityName),
            $flattenedData
        ));
    }
}
