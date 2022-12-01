<?php

namespace App\Controller\Admin;

use App\Entity\Approvisionnement;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class ApprovisionnementCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Approvisionnement::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('projet'),
        ];
    }
}
