<?php

namespace App\Form;

use App\Entity\Observation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ObservationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('message' , TextareaType::class , [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Renseignez votre observation ici'
                ]
            ])
            ->add('date' , DateType::class , [
                'label' => false,
            ])
            // ->add('auteur')
            // ->add('projet')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Observation::class,
        ]);
    }
}
