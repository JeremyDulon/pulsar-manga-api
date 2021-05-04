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
     * @ORM\OneToMany(targetEntity=UserMangaLanguage::class, mappedBy="user", orphanRemoval=true)
     */
    private $userMangaLanguages;

    /**
     *
     * @var array|string[]
     * @ORM\Column(name="languages", type="simple_array", nullable=false)
     * @Serializer\Groups({ "getUser" })
     */
    private $languages = [ PlatformUtil::LANGUAGE_EN ];

    /**
     * User constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->roles = [ self::ROLE_USER ];
        $this->languages = [ PlatformUtil::LANGUAGE_EN ];
        $this->deleted = false;
        $this->userMangas = new ArrayCollection();
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
     * @return Collection|UserMangaLanguage[]
     */
    public function getUserMangaPlatforms(): Collection
    {
        return $this->userMangas;
    }

    public function addUserManga(UserManga $userManga): self
    {
        if (!$this->userMangas->contains($userManga)) {
            $this->userMangas[] = $userManga;
            $userManga->setUser($this);
        }

        return $this;
    }

    public function removeUserManga(UserManga $userManga): self
    {
        if ($this->userMangas->removeElement($userManga)) {
            // set the owning side to null (unless already changed)
            if ($userManga->getUser() === $this) {
                $userManga->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @param MangaPlatform $mangaPlatform
     * @return UserMangaPlatform|bool
     */
    public function isFavorite(MangaPlatform $mangaPlatform) {
        /** @var UserMangaPlatform $userMangaPlatform */
        foreach ($this->userMangaPlatforms as $userMangaPlatform) {
            if ($userMangaPlatform->getMangaPlatform() === $mangaPlatform) {
                return $userMangaPlatform;
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
        return array_map(function (UserMangaLanguage $userMangaPlatform) {
            $manga = $userMangaPlatform->getMangaPlatform()->getManga();
            return [
                'chapter' => $userMangaPlatform->getLastChapter()->getId(),
                'page' => $userMangaPlatform->getLastPage(),
                'slug' => $manga->getSlug(),
                'favorite' => $userMangaPlatform->getFavorite(),
                'id' => $userMangaPlatform->getMangaPlatform()->getId()
            ];
        }, $this->userMangaPlatforms->toArray());
    }
}
