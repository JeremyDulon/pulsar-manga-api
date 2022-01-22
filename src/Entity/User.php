<?php
namespace App\Entity;

use App\Utils\PlatformUtil;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * User
 *
 * @ORM\Table(name="user", indexes={
 *     @ORM\Index(name="search_idx_username", columns={"username"}),
 *     @ORM\Index(name="search_idx_email", columns={"email"}),
 * })
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 *
 * @UniqueEntity(fields={"email"}, errorPath="email", message="EMAIL_IS_ALREADY_IN_USE")
 * @UniqueEntity(fields={"username"}, errorPath="username", message="USERNAME_IS_ALREADY_IN_USE")
 *
 */
class User extends BaseUser
{
    const ROLE_ADMIN = "ROLE_ADMIN";
    const ROLE_USER = "ROLE_USER";

    /**
     * To validate supported roles
     *
     * @var array
     */
    static public $ROLES_SUPPORTED = array(
        self::ROLE_SUPER_ADMIN => self::ROLE_SUPER_ADMIN,
        self::ROLE_ADMIN => self::ROLE_ADMIN,
        self::ROLE_USER => self::ROLE_USER,
    );

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="FIELD_CAN_NOT_BE_EMPTY")
     * @Assert\Email(
     *     message = "INCORRECT_EMAIL_ADDRESS",
     *     checkMX = true
     * )
     * @Serializer\Groups({ "postUser", "getUser" })
     */
    protected $email;

    /**
     * @var string
     * @Serializer\Groups({
     *     "postManager", "putUser", "postUser", "postAgent"
     * })
     * @Serializer\Accessor(setter="setPlainPassword", getter="getPassword")
     */
    protected $password;

    /**
     * @var string
     *
     * @Serializer\Groups({ "postUser", "getUser" })
     */
    protected $username;

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=100, nullable=true)
     *
     * @Assert\Length(
     *      min = 1,
     *      max = 100,
     *      minMessage = "FIELD_LENGTH_TOO_SHORT",
     *      maxMessage = "FIELD_LENGTH_TOO_LONG"
     * )
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=100, nullable=true)
     *
     * @Assert\Length(
     *      min = 1,
     *      max = 100,
     *      minMessage = "FIELD_LENGTH_TOO_SHORT",
     *      maxMessage = "FIELD_LENGTH_TOO_LONG"
     * )
     */
    private $lastName;

    /**
     *
     * @var array|string[]
     * @Serializer\Groups({ "getUser" })
     */
    protected $roles = [self::ROLE_DEFAULT];

    /**
     * @var boolean
     *
     * @ORM\Column(name="deleted", type="boolean")
     *
     * @Assert\Type(
     *     type="bool",
     *     message="FIELD_MUST_BE_BOOLEAN_TYPE"
     * )
     */
    private $deleted;

    /**
     * @ORM\OneToMany(targetEntity=UserComicLanguage::class, mappedBy="user", orphanRemoval=true)
     */
    private $userComicLanguages;

    /**
     *
     * @var array|string[]
     * @ORM\Column(name="languages", type="simple_array", nullable=false)
     * @Serializer\Groups({ "getUser" })
     */
    private $languages;

    /**
     * User constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->roles = [ self::ROLE_USER ];
        $this->languages = [ PlatformUtil::LANGUAGE_EN, PlatformUtil::LANGUAGE_FR ];
        $this->deleted = false;
        $this->userComicLanguages = new ArrayCollection();
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }

    /**
     * @return Collection|UserComicLanguage[]
     */
    public function getUserComicLanguages(): Collection
    {
        return $this->userComicLanguages;
    }

    /**
     * @param UserComicLanguage $userComicLanguage
     * @return User
     */
    public function addUserComicLanguages(UserComicLanguage $userComicLanguage): User
    {
        if ($this->userComicLanguages->contains($userComicLanguage)) {
            $this->userComicLanguages[] = $userComicLanguage;
            $userComicLanguage->setUser($this);
        }

        return $this;
    }

    /**
     * @param UserComicLanguage $userComicLanguage
     * @return User
     */
    public function removeUserComicLanguages(UserComicLanguage $userComicLanguage): User
    {
        if ($this->userComicLanguages->contains($userComicLanguage)) {
            $this->userComicLanguages->removeElement($userComicLanguage);
            // set the owning side to null (unless already changed)
            if ($userComicLanguage->getUser() === $this) {
                $userComicLanguage->setUser(null);
            }
        }
        return $this;
    }

    /**
     * @return array|string[]
     */
    public function getLanguages(): array
    {
        return $this->languages;
    }

    /**
     * @param array|string[] $languages
     * @return User
     */
    public function setLanguages(array $languages): User
    {
        $this->languages = $languages;
        return $this;
    }



//    TODO: isFavorite
//    TODO: getFavorites

    /**
     * @param ComicLanguage $ComicLanguage
     * @return UserComicLanguage|bool
     */
    public function isFavorite(ComicLanguage $ComicLanguage) {
        /** @var UserComicLanguage $userComicLanguage */
        foreach ($this->userComicLanguages as $userComicLanguage) {
            if ($userComicLanguage->getComicLanguge() === $ComicLanguage) {
                return $userComicLanguage;
            }
        }
        return false;
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\Groups({ "getUser" })
     * @Serializer\SerializedName("favorites")
     * @Serializer\Expose
     */
    public function getFavorites(): array
    {
        return array_map(function (UserComicLanguage $userComicPlatform) {
            $Comic = $userComicPlatform->getComicPlatform()->getComic();
            return [
                'chapter' => $userComicPlatform->getLastChapter()->getId(),
                'page' => $userComicPlatform->getLastPage(),
                'slug' => $Comic->getSlug(),
                'favorite' => $userComicPlatform->getFavorite(),
                'id' => $userComicPlatform->getComicPlatform()->getId()
            ];
        }, $this->userComicPlatforms->toArray());
    }
}
