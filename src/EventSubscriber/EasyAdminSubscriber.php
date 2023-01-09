<?php

namespace App\EventSubscriber;

use App\Entity\Comic;
use App\Entity\ComicLanguage;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EasyAdminSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            BeforeEntityPersistedEvent::class => ['setRelatedEntities']
        ];
    }

    public function setRelatedEntities(BeforeEntityPersistedEvent $event)  {
        $entity = $event->getEntityInstance();

        if ($entity instanceof Comic) {
            foreach ($entity->getComicLanguages() as $comicLanguage) {
                /** @var $comicLanguage ComicLanguage */
                $comicLanguage->setComic($entity);
            }
        }
    }
}