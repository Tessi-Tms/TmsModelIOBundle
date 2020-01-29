<?php

/**
 * @author Nabil Mansouri <nabil.mansouri@tessi.fr>
 */

namespace Tms\Bundle\ModelIOBundle\Handler;

use Tms\Bundle\MediaClientBundle\Model\Media;
use Tms\Bundle\MediaClientBundle\StorageProvider\TmsMediaStorageProvider;

class MediaHandler
{
    /**
     * @var TmsMediaStorageProvider
     */
    private $storageProvider;

    /**
     * Constructor.
     *
     * @param TmsMediaStorageProvider $storageProvider
     */
    public function __construct(TmsMediaStorageProvider $storageProvider)
    {
        $this->storageProvider = $storageProvider;
    }

    /**
     * Is a Media
     *
     * @param mixed $reflection
     *
     * @return boolean
     */
    public static function isMedia($object)
    {
        if ($object instanceof Media) {
            return true;
        }

        return false;
    }

    /**
     * Import Media
     *
     * @param mixed $object
     *
     * @return mixed
     */
    public function importMedia($object)
    {
        try {
            return $this
                ->storageProvider
                ->cloneMedia($object);
        } catch (\Exception $e) {
        }

        return $object;
    }
}
