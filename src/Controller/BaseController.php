<?php

namespace App\Controller;

use App\Service\ValidationService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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

    /**
     * @var ValidatorInterface
     */
    private $validation = null;

    public function __construct(EntityManagerInterface $em, ValidationService $validation) {
        $this->em = $em;
        $this->validation = $validation;
    }

    /**
     * @param $subject
     * @see ValidationService validate
     */
    public function validate($subject)
    {
        $this->validation->validate($subject);
    }
}
