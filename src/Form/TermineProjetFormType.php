<?php

namespace App\Form;

use App\Entity\Projet;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TermineProjetFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ->add('nom')
            // ->add('demarrage')
            // ->add('delai')
            ->add('fin' , DateType::class , [
                'label' => false,
            ])
            // ->add('budget')
            // ->add('est_termine')
            // ->add('code')
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
