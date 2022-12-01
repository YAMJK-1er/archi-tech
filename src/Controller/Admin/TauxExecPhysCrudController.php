<?php

namespace App\Controller\Admin;

use App\Entity\TauxExecPhys;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\PercentField;

class TauxExecPhysCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TauxExecPhys::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            yield AssociationField::new('projet'),
            yield IntegerField::new('delai'),
            yield IntegerField::new('duree'),
            yield PercentField::new('taux')->setNumDecimals(2),
        ];
    }
}
