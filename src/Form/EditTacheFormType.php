<?php

namespace App\Form;

use App\Entity\Tache;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditTacheFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ->add('intitule')
            // ->add('debut_prev')
            // ->add('delai')
            // ->add('cout_base')
            ->add('debut_reel' , DateType::class , [
                'label' => false,
            ])
            ->add('date_fin' , DateType::class , [
                'label' => false,
            ])
            ->add('cout_reel' , IntegerType::class , [
                'label' => false,
                'attr' => [
                    'placeholder' => 'En Franc CFA' ,
                    'class' => 'champ',
                ]
            ])
            ->add('est_realise' , CheckboxType::class , [
                'label' => 'La tâche est réalisée',
            ])
            // ->add('planning')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tache::class,
        ]);
    }
}
