<?php

namespace App\EventListener;

use App\Entity\File;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Gaufrette\Filesystem;

class FileDeletedCleaner
{
    private Filesystem $filesystem;

    public function __construct(
        Filesystem $filesystem
    )
    {
        $this->filesystem = $filesystem;
    }

    public function preRemove(File $file, LifecycleEventArgs $eventArgs): void
    {
        if ($file->getPath() !== null) {
            $adapter = $this->filesystem->getAdapter();
            $adapter->delete($file->getPath());
        }
    }
}