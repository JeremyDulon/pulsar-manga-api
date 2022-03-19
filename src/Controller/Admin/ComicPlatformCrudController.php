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
            ->setSearchFields(['id', 'url', 'weight']);
    }

    public function configureFields(string $pageName): iterable
    {
        $id = IntegerField::new('id', 'ID');
        $weight = IntegerField::new('weight');
        $url = UrlField::new('url');
        $platform = AssociationField::new('platform');
        $comicTitle = TextareaField::new('comic.title');
        $platformName = TextareaField::new('platform.name');
        $updatedAt = TextareaField::new('updatedAt');
        $chapters = TextareaField::new('chapters');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $comicTitle, $platformName, $url, $updatedAt, $chapters];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $url, $weight, $platform];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$id, $weight, $url];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$id, $weight, $url];
        }
    }
}
