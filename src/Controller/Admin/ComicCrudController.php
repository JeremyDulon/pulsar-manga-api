<?php

namespace App\Controller\Admin;

use App\Entity\Comic;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ComicCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Comic::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Comic')
            ->setEntityLabelInPlural('Comic')
            ->setSearchFields(['id', 'title', 'altTitles', 'slug', 'type', 'status', 'author']);
    }

    public function configureFields(string $pageName): iterable
    {
        $title = TextField::new('title');
        $altTitles = ArrayField::new('altTitles');
        $status = IntegerField::new('status');
        $id = IntegerField::new('id', 'ID');
        $slug = TextField::new('slug');
        $type = IntegerField::new('type');
        $author = TextField::new('author');
        $lastUpdated = DateTimeField::new('lastUpdated');
        $createdAt = DateTimeField::new('createdAt');
        $updatedAt = DateTimeField::new('updatedAt');
        $image = AssociationField::new('image');
        $comicLanguages = AssociationField::new('comicLanguages');
        $imageUrl = ImageField::new('image.url');
        $platforms = TextareaField::new('platforms');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $title, $altTitles, $status, $imageUrl, $createdAt, $platforms];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $title, $altTitles, $slug, $type, $status, $author, $lastUpdated, $createdAt, $updatedAt, $image, $comicLanguages];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$title, $altTitles, $status];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$title, $altTitles, $status];
        }
    }
}
