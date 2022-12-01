<?php

namespace App\Controller\Admin;

use App\Entity\Element;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ElementCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Element::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('approvisionnement'),
            TextField::new('nom'),
            TextField::new('UniteOeuvre'),
            IntegerField::new('stock_restant'),
            IntegerField::new('quantite_globale'),
        ];
    }
}
