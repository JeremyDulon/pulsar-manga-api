<?php


namespace App\EventListener;

use App\Entity\File;
use App\Service\ImageService;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class UrlFileListener
{
    /**
     * @var ImageService $imageService
     */
    private $imageService;

    public function __construct(ImageService $imageService) {
        $this->imageService = $imageService;
    }
    // the entity listener methods receive two arguments:
    // the entity instance and the lifecycle event
    public function postLoad(File $file, LifecycleEventArgs $event)
    {
        $url = $file->getExternalUrl() ?? $this->imageService->getFileUrl($file);
        $file->setUrl($url);
        // ... do something to notify the changes
    }
}
