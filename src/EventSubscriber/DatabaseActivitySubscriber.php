<?php

namespace App\EventSubscriber;

use App\Entity\Categorie;
use App\Entity\Fournisseur;
use App\Entity\Produit;
use App\Repository\CategorieRepository;
use App\Repository\FournisseurRepository;
use App\Repository\ProduitRepository;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\PostPersist;
use Doctrine\ORM\Mapping\PostUpdate;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Flasher\Prime\Flasher;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\String\Slugger\AsciiSlugger;

class DatabaseActivitySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private FournisseurRepository $fournisseurRepository, private Flasher $flasher,
        private CategorieRepository $categorieRepository, private ProduitRepository $produitRepository
    )
    {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::postUpdate,
            Events::postRemove
        ];
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        $message = false;

        if ($entity instanceof Fournisseur){
            $this->slug($args, 'fournisseur');

            $message = "Le fournisseur {$entity->getNom()} a été enregistré avec succès!";
        }

        if ($entity instanceof Categorie){
            $this->slug($args, 'categorie');
            $this->codeCategorie($args);
            $message = "La catégorie {$entity->getNom()} a été enregistrée avec succès!";
        }

        if ($entity instanceof Produit){
            $this->slug($args, 'produit');
            $this->codeProduit($args);
            $message = "Le produit '{$entity->getNom()}' a été ajouté avec succès!";
        }

        $this->messageFlasher($message);
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        $message = false;

        if ($entity instanceof Fournisseur){
            $this->slug($args, 'fournisseur');

            $message = "Le fournisseur '{$entity->getNom()}' a été modifié avec succès!";
        }

        if ($entity instanceof Categorie){
            $this->slug($args, 'categorie');
            $this->codeCategorie($args);
            $message = "La catégorie '{$entity->getNom()}' a été modifiée avec succès!";
        }

        if ($entity instanceof Produit){
            $this->slug($args, 'produit');
            $this->codeProduit($args);
            $message = "Le produit '{$entity->getNom()}' a été ajouté avec succès!";
        }

        $this->messageFlasher($message);
    }

    public function postRemove(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        $message = false;

        if ($entity instanceof Fournisseur){
            $message = "Le fournisseur {$entity->getNom()} a été supprimé avec succès!";
        }

        if ($entity instanceof Categorie){
            $message = "La catégorie '{$entity->getNom()}' a été supprimée avec succès!";
        }

        if ($entity instanceof Produit){
            $message = "Le produit '{$entity->getNom()}' a été supprimé avec succès!";
        }

        $this->messageFlasher($message);
    }

    public function slug(LifecycleEventArgs $args, string $entityName)
    {
        $entity = $args->getObject();
        $repository = "{$entityName}Repository";

        // Generation du slug
        $slugify = new AsciiSlugger();
        $slug = $slugify->slug(strtolower($entity->getNom()));
        $entity->setSlug($slug);

        return $this->$repository->save($entity, true);
    }

    public function messageFlasher($message = null)
    {
        if ($message){
            return $this->flasher
                ->options([
                    'timer' => 3000,
                    'position' => 'top-center'
                ])
                ->addSuccess($message);
        }

        return;
    }

    public function codeCategorie(LifecycleEventArgs $args): bool
    {
        $entity = $args->getObject();
        $id = $entity->getId();
        if ($id < 10) $code = "10{$id}";
        else $code = "1{$id}";
        $entity->setCode( (int) $code);
        $this->categorieRepository->save($entity, true);

        return true;
    }

    public function codeProduit(LifecycleEventArgs $args): bool
    {
        $entity = $args->getObject(); //dd($entity);
        $id = $entity->getId();
        if (10 > $id) $code = "0{$id}";
        else $code = $id;

        $entity->setCode($entity->getCategorie()->getCode().''.$code);
        $this->produitRepository->save($entity, true);

        return true;
    }
}