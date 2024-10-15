<?php

namespace App\Controller\Admin;

use App\Entity\ComicIssue;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ComicIssueCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ComicIssue::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('ComicIssue')
            ->setEntityLabelInPlural('ComicIssue')
            ->setSearchFields(['id', 'title', 'number', 'type']);
    }

    public function configureFields(string $pageName): iterable
    {
        $title = TextField::new('title', 'Issue Title');
        $number = NumberField::new('number');
        $date = DateTimeField::new('date');
        $date->setFormat(DateTimeField::FORMAT_MEDIUM);
        $id = IntegerField::new('id', 'ID');
        $type = IntegerField::new('type');
        $createdAt = DateTimeField::new('createdAt');
        $updatedAt = DateTimeField::new('updatedAt');
        $comic = AssociationField::new('comic');
        $comicTitle = TextField::new('comicLanguage.comic.title', 'Title');
        $comicPages = AssociationField::new('comicPages');
        $comicPlatformLanguage = TextField::new('platformAndLanguage', 'PlatformLanguage');
        $quality = ChoiceField::new('quality');

        $quality->setChoices([
            'Good' => ComicIssue::QUALITY_GOOD,
            'Poor' => ComicIssue::QUALITY_POOR,
            'Bad' => ComicIssue::QUALITY_BAD,
            'Error' => ComicIssue::QUALITY_ERROR
        ])->renderAsBadges([
            ComicIssue::QUALITY_GOOD => 'success',
            ComicIssue::QUALITY_POOR => 'secondary',
            ComicIssue::QUALITY_BAD => 'warning',
            ComicIssue::QUALITY_ERROR => 'danger',

        ]);

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $comicTitle, $comicPlatformLanguage, $title, $number, $quality, $comicPages, $date];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $title, $number, $type, $quality, $date, $createdAt, $updatedAt, $comic];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$title, $number, $date];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$title, $number, $date];
        }
    }
}
