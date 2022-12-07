<?php

namespace App\Form;

use App\Entity\Depense;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DepensesFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateType::class, [
                'label' => false,
            ])
            ->add('intitule', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Intitulé de la dépense',
                    'class' => 'champ',
                ]
            ])
            ->add('unite', NumberType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Coût',
                    'class' => 'champ',
                ]
            ])
            ->add('quantite', IntegerType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Quantité (facultatif)',
                    'class' => 'champ',
                ],

                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Depense::class,
        ]);
    }
}
