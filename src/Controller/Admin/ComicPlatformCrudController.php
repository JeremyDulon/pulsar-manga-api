<?php

namespace App\Controller\Admin;

use App\Entity\ComicPlatform;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
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
        $status = ChoiceField::new('status');
        $url = UrlField::new('url');
        $platform = AssociationField::new('platform');
        $platformName = TextareaField::new('platform.name', 'Platform');
        $comicLanguage = AssociationField::new('comicLanguage');
        $comicTitle = TextareaField::new('comicLanguage.comic.title', 'Title');

        $status->setChoices([
            'Actif' => ComicPlatform::STATUS_ENABLED,
            'En suspens' => ComicPlatform::STATUS_SUSPENDED,
            'Inactif' => ComicPlatform::STATUS_DISABLED
        ]);

        $newEdit = [$url, $comicLanguage, $platform, $status];

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $comicTitle, $platformName, $url, $weight, $status];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $url, $platformName, $status];
        } elseif (Crud::PAGE_NEW  === $pageName) {
            return $newEdit;
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return $newEdit;
        }
    }
}
