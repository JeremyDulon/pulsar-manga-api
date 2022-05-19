<?php

namespace App\Controller\Admin;

use App\Entity\ComicIssue;
use App\Entity\ComicPage;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ComicPageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ComicPage::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('ComicPage')
            ->setEntityLabelInPlural('ComicPage')
            ->setSearchFields(['id', 'comicIssue.title']);
    }

    public function configureFields(string $pageName): iterable
    {
        $number = NumberField::new('number');
        $id = IntegerField::new('id', 'ID');
        $imageUrl = ImageField::new('file.url', 'Image');
        $comicIssueTitle = TextField::new('comicIssue.title');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $number, $comicIssueTitle, $imageUrl];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $number, $imageUrl];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$number, $imageUrl];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$number, $imageUrl];
        }
    }
}
