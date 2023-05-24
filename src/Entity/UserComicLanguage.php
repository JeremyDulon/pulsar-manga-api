<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'user_comic_language')]
#[ORM\UniqueConstraint(name: 'unique_user_comic_language', columns: [ 'comic_Language_id', 'user_id' ])]
#[ApiResource(
    collectionOperations: [
        'post',
        'get' => [
            'normalization_context' => ['groups' => ['list:UserComicLanguage', 'list:ComicLanguage', 'list:Comic', 'read:File']],
        ]
    ],
    itemOperations: ['get', 'put']
)]
#[ApiFilter(SearchFilter::class, properties: ['user' => 'exact'])]

class UserComicLanguage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups([ 'list:UserComicLanguage' ])]
    private int $id;

    #[ORM\ManyToOne(targetEntity: ComicLanguage::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([ 'list:UserComicLanguage' ])]
    private ComicLanguage $comicLanguage;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'userComicLanguages')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([ 'list:UserComicLanguage' ])]
    private User $user;

    #[ORM\ManyToOne(targetEntity: ComicIssue::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[Groups([ 'list:UserComicLanguage' ])]
    private ?ComicIssue $lastComicIssue;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups([ 'list:UserComicLanguage' ])]
    private int $lastPage;

    #[ORM\Column(type: 'boolean')]
    #[Groups([ 'list:UserComicLanguage' ])]
    private bool $favorite = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return ComicLanguage
     */
    public function getComicLanguage(): ComicLanguage
    {
        return $this->comicLanguage;
    }

    /**
     * @param ComicLanguage $comicLanguage
     * @return UserComicLanguage
     */
    public function setComicLanguage(ComicLanguage $comicLanguage): self
    {
        $this->comicLanguage = $comicLanguage;
        return $this;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return UserComicLanguage
     */
    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getLastComicIssue(): ?ComicIssue
    {
        return $this->lastComicIssue;
    }

    /**
     * @param ComicIssue $lastComicIssue
     * @return UserComicLanguage
     */
    public function setLastComicIssue(ComicIssue $lastComicIssue): self
    {
        $this->lastComicIssue = $lastComicIssue;
        return $this;
    }

    /**
     * @return int
     */
    public function getLastPage(): int
    {
        return $this->lastPage;
    }

    /**
     * @param int $lastPage
     * @return UserComicLanguage
     */
    public function setLastPage(int $lastPage): self
    {
        $this->lastPage = $lastPage;
        return $this;
    }

    /**
     * @return bool
     */
    public function isFavorite(): bool
    {
        return $this->favorite;
    }

    /**
     * @param bool $favorite
     * @return UserComicLanguage
     */
    public function setFavorite(bool $favorite): UserComicLanguage
    {
        $this->favorite = $favorite;
        return $this;
    }
}
