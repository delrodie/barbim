<?php

namespace App\Form;

use App\Entity\Produit;
use App\Entity\Recette;
use App\Entity\Vente;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
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
            ->add('stockFinal', IntegerType::class,[
                'attr' => ['class' => 'form-control'],
                'label' => "Stock restant"
            ])
            /*->add('montant', TextType::class,[
                'attr' => ['class' => "form-control", 'autocomplete' => 'off']
            ])*/
            //->add('benefice')
            //->add('createdAt')
            ->add('produit', EntityType::class,[
                'attr' => ['class' => 'form-select select2'],
                'class' => Produit::class,
                'query_builder' => function (EntityRepository $er){
                    return $er->createQueryBuilder('produit')->orderBy('produit.nom', "ASC");
                },
                'placeholder' => ''
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
