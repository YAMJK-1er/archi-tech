<?php

namespace App\Controller\Admin;

use App\Entity\TauxExecFin;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\PercentField;

class TauxExecFinCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TauxExecFin::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            yield AssociationField::new('projet'),
            yield IntegerField::new('budget'),
            yield IntegerField::new('depenses'),
            yield PercentField::new('taux')->setNumDecimals(2),
        ];
    }
}
