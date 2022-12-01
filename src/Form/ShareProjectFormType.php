<?php

namespace App\Form;

use App\Entity\Projet;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShareProjectFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Nom du projet',
                ]
            ])
            ->add('code', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Code du projet',
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => false,
                'mapped' => false,
                'attr' => [
                    'placeholder' => 'Adresse email de l\'invité'
                ]
            ])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Projet::class,
        ]);
    }
}
