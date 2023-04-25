<?php

namespace App\Form;

use App\Entity\Fournisseur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FournisseurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class,[
                'attr'=>['class'=>'form-control', 'placeholder'=>"Raison sociale du fournisseur", 'autocomplete'=>"off"],
                'required' => true
            ])
            ->add('type', ChoiceType::class,[
                'attr' => ['class'=>'form-select'],
                'choices' => [
                    '-- Selectionnez --' => '',
                    'Depôt' => 0,
                    'Sous-depôt' => 1,
                ],
                'required' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Fournisseur::class,
        ]);
    }
}
