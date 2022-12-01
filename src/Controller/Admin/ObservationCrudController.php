<?php

namespace App\Controller\Admin;

use App\Entity\Observation;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ObservationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Observation::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('projet'),
            TextField::new('auteur'),
            TextareaField::new('message'),
            DateField::new('date'),
        ];
    }
}
