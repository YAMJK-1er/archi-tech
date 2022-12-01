<?php

namespace App\Controller\Admin;

use App\Entity\Planning;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class PlanningCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Planning::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('projet'),
        ];
    }
}
