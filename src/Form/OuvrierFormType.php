<?php

namespace App\Form;

use App\Entity\Ouvrier;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OuvrierFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Nom de l\'ouvrier'
                ]
            ])
            ->add('fonction', ChoiceType::class, [
                'label' => false,

                'choices' => [
                    '' => '',
                    'Maçon' => 'Maçon',
                    'Menuisier' => 'Menuisier',
                    'Peintre' => 'Peintre'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ouvrier::class,
        ]);
    }
}
