<?php

namespace App\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;

/**
 * Class BaseController
 * @package App\Controller
 */
class BaseController extends AbstractFOSRestController
{
    /**
     * @var EntityManager
     */
    protected $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }
}
