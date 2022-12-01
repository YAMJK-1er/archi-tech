<?php

namespace App\Controller\Admin;

use App\Entity\Tache;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TacheCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Tache::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('planning'),
            TextField::new('intitule'),
            DateField::new('debut_prev'),
            IntegerField::new('delai'), 
            IntegerField::new('cout_base'),
            DateField::new('debut_reel'),
            DateField::new('date_fin'),
            IntegerField::new('cout_reel'),
            BooleanField::new('est_realise'),
        ];
    }
}
