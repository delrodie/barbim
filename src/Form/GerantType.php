<?php

namespace App\Form;

use App\Entity\Gerant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GerantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            //->add('matricule')
            ->add('nom', TextType::class,[
                'attr' => ['class'=>'form-control', 'placeholder'=>"Nom et prenoms du gérant", 'autocomplete'=>"off"]
            ])
            ->add('telephone', TelType::class,[
                'attr' => ['class'=>'form-control', 'placeholder'=>"Numero de telephone", 'autocomplete' => "off"]
            ])
            //->add('slug')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Gerant::class,
        ]);
    }
}
