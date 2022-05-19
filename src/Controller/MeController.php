<?php

namespace App\Controller;

use Symfony\Component\Security\Core\Security;

class MeController
{
    /** @var Security $security */
    private $security;

    public function __construct(Security $security) {
        $this->security = $security;
    }

    public function __invoke() {
        $user = $this->security->getUser();
        return $user;
    }

}