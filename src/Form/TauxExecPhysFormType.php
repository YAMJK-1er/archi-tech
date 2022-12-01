<?php

namespace App\Form;

use App\Entity\TauxExecPhys;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TauxExecPhysFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ->add('delai')
            ->add('duree' , IntegerType::class , [
                'label' => false ,
            ])
            // ->add('taux')
            // ->add('projet')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TauxExecPhys::class,
        ]);
    }
}
