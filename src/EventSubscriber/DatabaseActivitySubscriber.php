<?php

namespace App\EventSubscriber;

use App\Entity\Fournisseur;
use App\Repository\FournisseurRepository;
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
        private FournisseurRepository $fournisseurRepository, private Flasher $flasher
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

        $this->messageFlasher($message);
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        $message = false;

        if ($entity instanceof Fournisseur){
            $this->slug($args, 'fournisseur');

            $message = "Le fournisseur {$entity->getNom()} a été modifié avec succès!";
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
}
