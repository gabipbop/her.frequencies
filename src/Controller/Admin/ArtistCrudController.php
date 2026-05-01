<?php

namespace App\Controller\Admin;

use App\Entity\Artist;
use App\Entity\Category;
use App\Entity\Link;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ArtistCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Artist::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Nom'),
            TextField::new('country', 'Pays'),
            TextField::new('city', 'Ville'),
            TextareaField::new('description')->hideOnIndex(),
            ImageField::new('picture', 'Photo')
                ->setBasePath('/uploads/artists')
                ->setUploadDir('public/uploads/artists')
                ->setRequired(false),
            AssociationField::new('categories', 'Catégories'),
            AssociationField::new('links', 'Liens'),
            DateTimeField::new('acceptedAt', 'Accepté le')->hideOnForm(),
        ];
    }
}
