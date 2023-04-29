<?php

namespace App\EventSubscriber;

use App\Entity\Categorie;
use App\Entity\Commande;
use App\Entity\Fournisseur;
use App\Entity\Produit;
use App\Repository\CategorieRepository;
use App\Repository\CommandeRepository;
use App\Repository\FournisseurRepository;
use App\Repository\ProduitRepository;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\PostPersist;
use Doctrine\ORM\Mapping\PostUpdate;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Flasher\Prime\Flasher;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Exception\ValidatorException;

class DatabaseActivitySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private FournisseurRepository $fournisseurRepository, private Flasher $flasher,
        private CategorieRepository $categorieRepository, private ProduitRepository $produitRepository,
        private CommandeRepository $commandeRepository
    )
    {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::postUpdate,
            Events::postRemove,
            Events::preFlush,
        ];
    }

    public function preFlush(PreFlushEventArgs $args): void
    {
        $em = $args->getObjectManager();
        $unitOfWork = $em->getUnitOfWork();
        $entities = $unitOfWork->getScheduledEntityInsertions();

        foreach ($entities as $entity){
            if ($entity instanceof Commande) {
                $verif = $this->commandeRepository->findOneBy(['ref' => $entity->getRef(), 'fournisseur' => $entity->getFournisseur()]);
                if ($verif){
                    //$this->messageFlasher("");

                    $response = new RedirectResponse('/commande', 301);
                    //$message="test";

                    $this->flasher
                        ->options([
                            'timer' => 7000,
                            'position' => 'top-center'
                        ])
                        ->addError("Echef, cette commande a déjà été enregistrée {$response}");

                    throw new ValidatorException("Echec, cette commande a déjà été enregistrée");
                }
            }
        }


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

        if ($entity instanceof Commande){
            $this->commandeReste($args);
            $message = "La commande '{$entity->getRef()}' a été ajoutée avec succès!";
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

        if ($entity instanceof Commande){
            //$this->commandeReste($args);
            $message = "La commande '{$entity->getRef()}' a été modifiée avec succès!";
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

    public function commandeReste(LifecycleEventArgs $args): bool
    {
        $entity = $args->getObject();
        $entity->setReste($entity->getMontant());
        $entity->setFlag(0);
        $this->commandeRepository->save($entity, true);

        return true;
    }


}
