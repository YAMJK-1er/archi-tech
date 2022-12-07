<?php

namespace App\Form;

use App\Entity\Mouvement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MouvementFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateType::class, [
                'label' => false,
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    '' => '',
                    'Approvisionnement' => 'Approvisionnement',
                    'Consommation' => 'Consommation',
                ], 
                'label' => false,
            ])
            ->add('quantite', IntegerType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Quantité',
                ]
            ])
            // ->add('element')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Mouvement::class,
        ]);
    }
}
