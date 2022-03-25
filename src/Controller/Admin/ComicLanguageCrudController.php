<?php

namespace App\Controller\Admin;

use App\Entity\ComicLanguage;
use App\Utils\PlatformUtil;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class ComicLanguageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ComicLanguage::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('ComicLanguage')
            ->setEntityLabelInPlural('ComicLanguage')
            ->setSearchFields(['id', 'language', 'comic.title']);
    }

    public function configureFields(string $pageName): iterable
    {
        $id = IntegerField::new('id', 'ID');
        $language = ChoiceField::new('language');
        $description = TextareaField::new('description');
        $autoUpdate = BooleanField::new('autoUpdate');
        $comic = AssociationField::new('comic');
        $comicName = TextareaField::new('comic.title');

        $language->setChoices([
            'FR' => PlatformUtil::LANGUAGE_FR,
            'EN' => PlatformUtil::LANGUAGE_EN
        ]);

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $comicName, $language, $autoUpdate];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $comicName, $description, $language, $autoUpdate];
        } elseif (Crud::PAGE_NEW  === $pageName) {
            return [$comic, $description, $language, $autoUpdate];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$comic, $description, $language, $autoUpdate];
        }
    }
}
