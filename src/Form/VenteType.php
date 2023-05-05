<?php

namespace App\Form;

use App\Entity\Recette;
use App\Entity\Vente;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VenteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            //->add('quantite')
            //->add('stockInitial')
            //->add('stockFinal')
            ->add('montant', TextType::class,[
                'attr' => ['class' => "form-control", 'autocomplete' => 'off']
            ])
            //->add('benefice')
            //->add('createdAt')
            ->add('produit', null,[
                'attr' => ['class' => 'form-select select2']
            ])
            ->add('recette', EntityType::class,[
                'attr' => ['class'=>'form-control'],
                'class' => Recette::class,
                'query_builder' => function (EntityRepository $er) use($options){
                        return $er->createQueryBuilder('r')
                            ->where('r.id = :id')
                            ->setParameter('id', $options['recette']);
                },
                'choice_label' => 'code'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vente::class,
            'recette' => null
        ]);
    }
}
