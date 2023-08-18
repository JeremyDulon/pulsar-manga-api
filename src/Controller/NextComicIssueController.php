<?php

namespace App\Controller;

use App\Entity\ComicIssue;
use App\Repository\ComicIssueRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class NextComicIssueController extends AbstractController
{
    public function __construct(
        private ComicIssueRepository $comicIssueRepository
    ) {}

    public function __invoke(ComicIssue $comicIssue) {
        return $this->comicIssueRepository->findNextComicIssue($comicIssue);
    }
}