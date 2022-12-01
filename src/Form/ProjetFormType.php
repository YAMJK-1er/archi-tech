<?php

namespace App\Form;

use App\Entity\Projet;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjetFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom' , TextType::class , [
                'label' => false,
                'attr' => ['class' => 'champ' , 'placeholder' => 'Nom du projet'] ,
            ])
            ->add('demarrage' , DateType::class , [
                'label' => false,
            ])
            ->add('delai' , IntegerType::class , [
                'label' => false,
                'attr' => ['class' => 'champ' , 'placeholder' => 'Delai (nombre de mois)'] ,
            ])
            // ->add('fin')
            ->add('budget' , IntegerType::class , [
                'label' => false,
                'attr' => ['class' => 'champ' , 'placeholder' => 'Budget(Franc CFA)'] ,
            ])
            // ->add('est_termine')
            // ->add('planning')
            // ->add('approvisionnement')
            // ->add('user')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Projet::class,
        ]);
    }
}
