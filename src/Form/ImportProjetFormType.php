<?php

namespace App\Form;

use App\Entity\Projet;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImportProjetFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom' , TextType::class ,[
                'label' => false,
                'attr' => ['placeholder' => 'Nom du projet']
            ])
            // ->add('demarrage')
            // ->add('delai')
            // ->add('fin')
            // ->add('budget')
            // ->add('est_termine')
            ->add('code' , TextType::class , [
                'label' => false,
                'attr' => ['placeholder' => 'Code du projet']
            ])
            // ->add('planning')
            // ->add('approvisionnement')
            // ->add('user')
            // ->add('tauxExecFin')
            // ->add('tauxExecPhys')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Projet::class,
        ]);
    }
}
