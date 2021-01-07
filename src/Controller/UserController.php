<?php


namespace App\Controller;

use App\Service\ValidationService;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Swift_Mailer;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class UserController extends BaseController
{
    /** @var UserManagerInterface */
    private $userManager;

    /** @var TokenGeneratorInterface */
    private $tokenGenerator;

    /** @var MailerInterface */
    private $mailer;

    public function __construct(
        EntityManagerInterface $em,
        ValidationService $validation,
        UserManagerInterface $userManager,
        TokenGeneratorInterface $tokenGenerator,
        Swift_Mailer $mailer
    )
    {
        parent::__construct($em, $validation);
        $this->userManager = $userManager;
        $this->tokenGenerator = $tokenGenerator;
        $this->mailer = $mailer;
    }


    /**
     * @Rest\Post("/user", name="add_user")
     * @Rest\View(serializerGroups={ "postUser" })
     *
     * @ParamConverter(
     *     "user",
     *     converter="fos_rest.request_body",
     *     options={
     *          "deserializationcContext" = {
     *              "groups" : { "postUser" }
     *          }
     *     }
     * )
     * @param User $user
     * @return User
     */
    public function addUserAction(User $user) {
        if (!$user->getId()) {
            $this->validate($user);
//            $token = $this->tokenGenerator->generateToken();

            $user->setEnabled(true);
            $user->setDeleted(false);
            $this->userManager->updatePassword($user);
            $this->userManager->updateUser($user);

            // TODO: Email verification ?

            return $user;
        }

        throw new HttpException(400, 'User already exists');
    }

    /**
     * @Rest\Get("/user", name="get_user")
     * @Rest\View(serializerGroups={ "getUser", "getUserManga", "mangaSlug" })
     *
     * @return User|UserInterface|null
     */
    public function getUserAction() {
        return $this->getUser();
    }
}
