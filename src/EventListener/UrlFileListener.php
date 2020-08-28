<?php


namespace App\EventListener;

use App\Entity\File;
use App\Service\ImageHelper;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class UrlFileListener
{
    /**
     * @var ImageHelper $imageHelper
     */
    private $imageHelper;

    public function __construct(ImageHelper $imageHelper) {
        $this->imageHelper = $imageHelper;
    }
    // the entity listener methods receive two arguments:
    // the entity instance and the lifecycle event
    public function postLoad(File $file, LifecycleEventArgs $event)
    {
        $url = $file->getExternalUrl() ?? $this->imageHelper->getFileUrl($file);
        $file->setUrl($url);
        // ... do something to notify the changes
    }
}
