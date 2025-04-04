<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Controller\MeController;
use App\Utils\PlatformUtil;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Annotation\ApiResource;

#[ORM\Entity]
#[ORM\Table(name: 'user')]
#[ORM\Index(columns: ['username'], name: 'search_idx_username')]
#[ORM\Index(columns: ['email'], name: 'search_idx_email')]
#[UniqueEntity(fields: ['email'], message: 'EMAIL_IS_ALREADY_IN_USE', errorPath: 'email')]
#[UniqueEntity(fields: ['username'], message: 'USERNAME_IS_ALREADY_IN_USE', errorPath: 'username')]
#[ApiResource(
    collectionOperations: [],
    itemOperations: [
        'get',
        'me' => [
            'path' => '/me',
            'method' => 'GET',
            'controller' => MeController::class,
            'security' => 'is_granted("ROLE_USER")',
            'identifiers' => [],
            'read' => false
        ]
    ],
    normalizationContext: [ 'groups' => [ 'read:User' ]]
)]
class User implements UserInterface
{
    const ROLE_ADMIN = "ROLE_ADMIN";
    const ROLE_USER = "ROLE_USER";

    /**
     * To validate supported roles
     *
     * @var array
     */
    static public $ROLES_SUPPORTED = array(
        self::ROLE_ADMIN => self::ROLE_ADMIN,
        self::ROLE_USER => self::ROLE_USER,
    );

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups([ 'read:User' ])]
    private int $id;

    #[ORM\Column(type: 'string', length: 180, unique: true, nullable: false)]
    #[Groups([ 'read:User' ])]
    private string $username;

    #[ORM\Column(type: 'string', length: 100, unique: true, nullable: false)]
    #[Assert\NotBlank(message: 'FIELD_CAN_NOT_BE_EMPTY')]
    #[Assert\Email(message: 'INCORRECT_EMAIL_ADDRESS')]
    #[Groups([ 'read:User' ])]
    protected string $email;

    #[ORM\Column(type: 'json')]
    #[Groups([ 'read:User' ])]
    private array $roles = [];

    #[ORM\Column(type: 'string')]
    private string $password;

    #[Assert\Type(type: 'bool', message: 'FIELD_MUST_BE_BOOLEAN_TYPE')]
    #[ORM\Column(type: 'boolean')]
    private bool $deleted;

    #[Assert\Type(type: 'bool', message: 'FIELD_MUST_BE_BOOLEAN_TYPE')]
    #[ORM\Column(type: 'boolean')]
    protected bool $enabled;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserComicLanguage::class, orphanRemoval: true)]
    private Collection $userComicLanguages;

    #[ORM\Column(type: 'simple_array', nullable: false)]
    #[Groups([ 'read:User' ])]
    private array $languages;

    public function __construct()
    {
        $this->languages = [ PlatformUtil::LANGUAGE_EN, PlatformUtil::LANGUAGE_FR ];
        $this->deleted = false;
        $this->userComicLanguages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = self::ROLE_USER;

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return User
     */
    public function setEmail(string $email): User
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     * @return User
     */
    public function setEnabled(bool $enabled): User
    {
        $this->enabled = $enabled;
        return $this;
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
//     * @param ComicLanguage $ComicLanguage
//     * @return UserComicLanguage|bool
//     */
//    public function isFavorite(ComicLanguage $ComicLanguage) {
//        /** @var UserComicLanguage $userComicLanguage */
//        foreach ($this->userComicLanguages as $userComicLanguage) {
//            if ($userComicLanguage->getComicLanguge() === $ComicLanguage) {
//                return $userComicLanguage;
//            }
//        }
//        return false;
//    }
//
//    /**
//     * @Serializer\VirtualProperty()
//     * @Serializer\Groups({ "getUser" })
//     * @Serializer\SerializedName("favorites")
//     * @Serializer\Expose
//     */
//    public function getFavorites(): array
//    {
//        return array_map(function (UserComicLanguage $userComicPlatform) {
//            $Comic = $userComicPlatform->getComicPlatform()->getComic();
//            return [
//                'chapter' => $userComicPlatform->getLastChapter()->getId(),
//                'page' => $userComicPlatform->getLastPage(),
//                'slug' => $Comic->getSlug(),
//                'favorite' => $userComicPlatform->getFavorite(),
//                'id' => $userComicPlatform->getComicPlatform()->getId()
//            ];
//        }, $this->userComicPlatforms->toArray());
//    }
}
