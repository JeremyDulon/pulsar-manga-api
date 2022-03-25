<?php

namespace App\Controller\Admin;

use App\Entity\ComicPlatform;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

class ComicPlatformCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ComicPlatform::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('ComicPlatform')
            ->setEntityLabelInPlural('ComicPlatform')
            ->setSearchFields(['id', 'url']);
    }

    public function configureFields(string $pageName): iterable
    {
        $id = IntegerField::new('id', 'ID');
        $weight = IntegerField::new('weight');
        $url = UrlField::new('url');
        $platform = AssociationField::new('platform');
        $platformName = TextareaField::new('platform.name', 'Platform');
        $updatedAt = TextareaField::new('updatedAt');
        $chapters = TextareaField::new('chapters');
        $comicLanguage = AssociationField::new('comicLanguage');
        $comicTitle = TextareaField::new('comicLanguage.comic.title', 'Title');

        $newEdit = [$url, $comicLanguage, $platform];

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $comicTitle, $platformName, $url, $updatedAt, $chapters];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $url, $platform];
        } elseif (Crud::PAGE_NEW  === $pageName) {
            return $newEdit;
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return $newEdit;
        }
    }
}
