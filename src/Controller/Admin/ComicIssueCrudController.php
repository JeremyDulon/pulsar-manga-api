<?php

namespace App\Controller\Admin;

use App\Entity\ComicIssue;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
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
        $title = TextField::new('title');
        $number = NumberField::new('number');
        $sourceUrl = TextareaField::new('sourceUrl');
        $date = DateTimeField::new('date');
        $id = IntegerField::new('id', 'ID');
        $type = IntegerField::new('type');
        $createdAt = DateTimeField::new('createdAt');
        $updatedAt = DateTimeField::new('updatedAt');
        $comic = AssociationField::new('comic');
        $comicPages = AssociationField::new('comicPages');
        $chapterPages = TextareaField::new('chapterPages');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $title, $number, $sourceUrl, $date, $chapterPages];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $title, $number, $type, $date, $createdAt, $updatedAt, $comic, $comicPages];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$title, $number, $sourceUrl, $date];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$title, $number, $sourceUrl, $date];
        }
    }
}
