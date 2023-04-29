<?php

namespace App\Form;

use App\Entity\Commande;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ref', TextType::class,[
                'attr'=>['class'=>'form-control', 'placeholder'=>"Référence", 'autocomplete'=>"off"],
                'label' => "N° Commande"
            ])
            ->add('montant', IntegerType::class,[
                'attr' => ['class'=>'form-control']
            ])
            ->add('dateCde', DateType::class,[
                'attr' => ['class'=>'form-control'],
                'widget'=> 'single_text',
                'html5' => true,
                'format' => 'yyyy-MM-dd'
            ])
            /*->add('flag', CheckboxType::class,[
                'attr' => ['class' => 'form-check-input'],
                'required' => false
            ])*/
            //->add('reste')
            //->add('createdAt')
            ->add('fournisseur', null,[
                'attr' => ['class' => 'form-control']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
        ]);
    }
}
