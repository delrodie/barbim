<?php

namespace App\Form;

use App\Entity\Recette;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecetteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateRecette', DateType::class,[
                'attr' => ['class'=>'form-control'],
                'widget'=> 'single_text',
                'html5' => true,
                'format' => 'yyyy-MM-dd'
            ])
            //->add('code')
            //->add('montant')
            //->add('createdAt')
            //->add('updatedAt')
            ->add('gerant', null,[
                'attr' => ['class' => 'form-control']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recette::class,
        ]);
    }
}
