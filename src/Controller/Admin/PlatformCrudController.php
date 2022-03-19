<?php

namespace App\Controller\Admin;

use App\Entity\Platform;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PlatformCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Platform::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Platform')
            ->setEntityLabelInPlural('Platform')
            ->setSearchFields(['id', 'name', 'baseUrl', 'status']);
    }

    public function configureFields(string $pageName): iterable
    {
        $id = IntegerField::new('id', 'ID');
        $name = TextField::new('name');
        $baseUrl = TextField::new('baseUrl');
        $status = IntegerField::new('status');
        $createdAt = DateTimeField::new('createdAt');
        $updatedAt = DateTimeField::new('updatedAt');
        $language = TextareaField::new('language');
        $mangaPath = TextareaField::new('mangaPath');
        $chapterPath = TextareaField::new('chapterPath');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $name, $language, $baseUrl, $mangaPath, $chapterPath];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $name, $baseUrl, $status, $createdAt, $updatedAt];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$id, $name, $baseUrl, $status];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$id, $name, $baseUrl, $status];
        }
    }
}
