<?php

namespace App\Form;

use App\Entity\TauxExecFin;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TauxExecFinFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ->add('budget')
            ->add('depenses' , IntegerType::class , [
                'label' => false,
            ])
            // ->add('taux')
            // ->add('projet')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TauxExecFin::class,
        ]);
    }
}
