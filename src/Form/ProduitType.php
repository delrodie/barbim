<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Produit;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            //->add('code')
            ->add('nom', TextType::class,[
                'attr'=>['class'=>'form-control', 'placeholder'=>"Nom du produit", 'autocomplete'=>"off"]
            ])
            ->add('montant', IntegerType::class,[
                'attr'=>['class'=>'form-control', 'placeholder'=>"Montant du produit", 'autocomplete'=>"off"]
            ])
            ->add('stock', IntegerType::class,[
                'attr'=>['class'=>'form-control', 'placeholder'=>"Quantité en stock", "readonly"=>true, 'disabled'=>true]
            ])
            //->add('slug')
            //->add('createdAt')
            //->add('updatedAt')
            ->add('categorie', EntityType::class,[
                'attr'=>['class'=>'form-select'],
                'class' => Categorie::class,
                'query_builder' => function(EntityRepository $er){
                    return $er->createQueryBuilder('c')->orderBy('c.nom', 'ASC');
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}
