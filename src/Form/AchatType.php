<?php

namespace App\Form;

use App\Entity\Achat;
use App\Entity\Commande;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AchatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('quantite', IntegerType::class,[
                'attr' => ['class'=>'form-control', 'autocomplete'=>"off"]
            ])
            //->add('stockInitial')
            //->add('stockFinal')
            ->add('montant', IntegerType::class,[
                'attr' => ['class'=>'form-control', 'autocomplete'=>"off"]
            ])
            //->add('benefice')
            ->add('produit', null,[
                'attr' => ['class' => 'form-control']
            ])
            ->add('commande', EntityType::class,[
                'attr' => ['class' => 'form-control'],
                'class' => Commande::class,
                'query_builder' => function (EntityRepository $er) use ($options){
                        return $er->createQueryBuilder('a')
                            ->where('a.id = :id')
                            ->setParameter('id', $options['commande'])
                            ;
                },
                //'data' => $options['commande'],
                'choice_label' => 'ref',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Achat::class,
            'commande' => null
        ]);
    }
}
