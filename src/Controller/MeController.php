<?php

namespace App\Controller;

use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class MeController
{
    /** @var Security $security */
    private Security $security;

    public function __construct(Security $security) {
        $this->security = $security;
    }

    public function __invoke(): ?UserInterface
    {
        $user = $this->security->getUser();
        return $user;
    }

}