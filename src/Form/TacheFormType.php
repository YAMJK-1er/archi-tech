<?php

namespace App\Form;

use App\Entity\Tache;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TacheFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('intitule' , TextType::class , [
                'label' => false ,
                'attr' => [
                    'placeholder' => 'Intitule de la tâche' ,
                    'class' => 'champ' , 
                ]
            ])
            ->add('debut_prev' , DateType::class , [
                'label' => false ,
            ])
            ->add('delai' , IntegerType::class ,[
                'label' => false,
                'attr' => [
                    'placeholder' => 'Delai d\'exécution (nombre de jours)' ,
                    'class' => 'champ' , 
                ]
            ])
            ->add('cout_base' , IntegerType::class , [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Cout prévisionnelle (Franc CFA)' ,
                    'class' => 'champ' , 
                ]
            ])
            // ->add('debut_reel')
            // ->add('date_fin')
            // ->add('cout_reel')
            // ->add('est_realise')
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
