<?php

namespace App\Controller\Admin;

use App\Entity\Book;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class BookCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Book::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('title', 'Название'),
            TextField::new('isbn', 'ISBN'),
            IntegerField::new('pageCount', 'Страниц'),
            TextField::new('status', 'Статус'),
            DateTimeField::new('publishedDate', 'Дата публикации'),
            TextareaField::new('shortDescription', 'Краткое описание')->hideOnIndex(),
            ImageField::new('imagePath', 'Обложка')
                ->setBasePath('/uploads/thumbnails')
                ->onlyOnIndex(),
            AssociationField::new('categories', 'Категории'),
        ];
    }
}
