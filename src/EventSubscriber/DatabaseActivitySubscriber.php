<?php

namespace App\EventSubscriber;

use App\Entity\Achat;
use App\Entity\Brasserie;
use App\Entity\Categorie;
use App\Entity\Commande;
use App\Entity\Fournisseur;
use App\Entity\Gerant;
use App\Entity\Produit;
use App\Entity\Recette;
use App\Entity\Vente;
use App\Repository\AchatRepository;
use App\Repository\BrasserieRepository;
use App\Repository\CategorieRepository;
use App\Repository\CommandeRepository;
use App\Repository\FournisseurRepository;
use App\Repository\GerantRepository;
use App\Repository\ProduitRepository;
use App\Repository\RecetteRepository;
use App\Repository\VenteRepository;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\PostPersist;
use Doctrine\ORM\Mapping\PostUpdate;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Flasher\Prime\Flasher;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
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
        private CommandeRepository $commandeRepository, private AchatRepository $achatRepository,
        private GerantRepository $gerantRepository, private RecetteRepository $recetteRepository,
        private VenteRepository $venteRepository, private BrasserieRepository $brasserieRepository
    )
    {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::postUpdate,
            Events::postRemove,
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

        if ($entity instanceof Brasserie){
            $this->slug($args, 'brasserie');
            $message = "La brasserie '{$entity->getNom()}' a été enregistrée avec succès!";
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

        if ($entity instanceof Achat){
            $this->achatSave($args);
            $message = "Le produit '{$entity->getProduit()->getNom()}' a été ajouté avec succès à la commande '{$entity->getCommande()->getRef()}'";
        }

        if ($entity instanceof Gerant){
            $this->slug($args, 'gerant');
            $this->matriculeGerant($args);
            $message = "Le gérant '{$entity->getNom()}' a été ajouté avec succès!";
        }

        if ($entity instanceof Recette){
            $this->codeRecette($args);
            $message = "La recette effectuée par le (la) gérant(e) '{$entity->getGerant()->getNom()}' a été ajoutée avec succès!";
        }

        if ($entity instanceof Vente){
            $this->venteSave($args);
            $message = "Le produit '{$entity->getProduit()->getNom()}' a été ajouté à la vente avec succès!";
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

        if ($entity instanceof Brasserie){
            $this->slug($args, 'brasserie');
            $message = "La brasserie '{$entity->getNom()}' a été modifiée avec succès!";
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

        if ($entity instanceof Gerant){
            $this->slug($args, 'gerant');
            //$this->matriculeGerant($args);
            $message = "Le gérant '{$entity->getNom()}' a été modifié avec succès!";
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

        if ($entity instanceof Brasserie){
            $message = "La brasserie '{$entity->getNom()}' a été supprimée avec succès!";
        }

        if ($entity instanceof Categorie){
            $message = "La catégorie '{$entity->getNom()}' a été supprimée avec succès!";
        }

        if ($entity instanceof Produit){
            $message = "Le produit '{$entity->getNom()}' a été supprimé avec succès!";
        }

        if ($entity instanceof Achat){
            $this->achatDelete($args);
            $message = "Le produit '{$entity->getProduit()->getNom()}' a été supprimé avec succès!";
        }

        if ($entity instanceof Recette){
            $message = "La recette '{$entity->getCode()} a été supprimée avec succès!";
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

    public function achatSave(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        // Variables
        $quantite = (int) $entity->getQuantite();
        $stock = (int) $entity->getProduit()->getStock();
        $prixVente = (int) $entity->getProduit()->getMontant();
        $montantAchat = (int) $entity->getMontant();
        $reste = (int) $entity->getCommande()->getReste();
        $stockInitial = $stock;
        $beneficeTotal = (int) $entity->getCommande()->getBenefice();

        // Calcul
        $stockFinal = $stockInitial + $quantite;
        $prixUnitaire =  $montantAchat /  $quantite;
        $prixVenteTotal = $quantite * $prixVente;
        $benefice = $prixVenteTotal - $montantAchat;
        $beneficeUnitaire = $prixVente - $prixUnitaire;
        $beneficeTotal += $benefice;

        // Mise a jour de la table produit
        $produit = $entity->getProduit();
        $produit->setStock($quantite + $stock);
        $this->produitRepository->save($produit, true);

        // Mise a jour de la table achat
        $entity->setStockInitial($stock);
        $entity->setStockFinal($stockFinal);
        $entity->setBenefice($benefice);
        $entity->setPrixUnitaire($prixUnitaire);
        $entity->setBeneficeUnitaire($beneficeUnitaire);
        $entity->setReste($quantite);
        $this->achatRepository->save($entity, true);

        // Mise a jour de la table Commande
        $commande = $entity->getCommande();
        $commande->setReste($reste - $montantAchat);
        $commande->setBenefice($beneficeTotal);
        $this->commandeRepository->save($commande, true);

        return true;
    }

    public function achatDelete(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        // Variables
        $quantite = (int) $entity->getQuantite();
        $stock = (int) $entity->getProduit()->getStock();
        $prixVente = (int) $entity->getProduit()->getMontant();
        $montantAchat = (int) $entity->getMontant();
        $reste = (int) $entity->getCommande()->getReste();
        $stockInitial = $stock;
        $beneficeTotal = (int) $entity->getCommande()->getBenefice();

        // Calcul
        $prixVenteTotal = $quantite * $prixVente;
        $benefice = $prixVenteTotal - $montantAchat;
        $beneficeTotal -= $benefice;

        // Mise a jour de la table produit
        $produit = $entity->getProduit();
        $produit->setStock($stock - $quantite);
        $this->produitRepository->save($produit, true);

        // Mise a jour de la table Commande
        $commande = $entity->getCommande();
        $commande->setReste($reste + $montantAchat);
        $commande->setBenefice($beneficeTotal);
        $this->commandeRepository->save($commande, true);

        return true;
    }

    public function matriculeGerant(LifecycleEventArgs $args): bool
    {
        $entity = $args->getObject();

        $entity->setMatricule(random_int(1001,9999));
        $this->gerantRepository->save($entity, true);

        return true;
    }

    public function codeRecette(LifecycleEventArgs $args)
    {
        $entity = $args->getObject(); //dd();
        $code = strtotime($entity->getDateRecette()->format('Y-m-d'));
        $entity->setCode(date('ymd', $code));
        $this->recetteRepository->save($entity, true);

        return true;
    }

    public function venteSave(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        // Variables
        $recette = $this->recetteRepository->findOneBy(['id' => $entity->getRecette()->getId()]);
        $produit = $this->produitRepository->findOneBy(['id' => $entity->getProduit()->getId()]);
        $montant = (int) $entity->getMontant();
        $stock = (int) $produit->getStock();
        $prixVente = (int) $produit->getMontant();
        $montantRecette = (int) $recette->getMontant();

        // Calcul
        $quantite = (int) $montant / $prixVente;
        $stock_final_produit = (int) $stock - $quantite;
        $montantRecette += $montant;

        $totem = false; $beneficeUnitaire=0; $i=0;

        while ($totem === false){

            $achats = $this->achatRepository->findOnlyOneWithResteSupZeroByProduit($entity->getProduit()->getId());

            $dernierAchat=[]; $reste=0;
            foreach ($achats as $achat){
                $beneficeUnitaire = (int) $achat->getBeneficeUnitaire();
                $reste = (int) $achat->getReste();
                $dernierAchat = $achat;
            }

            $variable = [
                'stock_final_produit' => $stock_final_produit,
                'montant_recette' => $montantRecette,
                'quantite' => $quantite,
                'stock_initial' => $stock,
            ];

            // Si la quantité vendue est inférieure au reste du dernier achat alors rechercher dans l'achat précédent
            if($reste >= $quantite ){
                $variable['stock_final_achat'] = $reste - $quantite ;
                $variable['benefice_total'] =  $quantite * $beneficeUnitaire;


                $this->majApresVente($args, $dernierAchat, $produit, $recette, $variable);

                $totem = true;
            }else{ //dd('ici');
                $variable['stock_final_achat'] = 0;
                $variable['benefice_total'] = $reste * $beneficeUnitaire;

                $this->majApresVente($args, $dernierAchat, $produit, $recette, $variable);
                $quantite -= $reste;
                $stock_final_produit -= $reste;


                $totem = false;
            }
        }

        return true;

    }

    public function majApresVente(LifecycleEventArgs $args, object $dernierAchat, object $produit, object $recette, array $variable)
    {
        $entity = $args->getObject();

        // mise a jour du dernier achat, du produit, de la recette et de la vente
        $dernierAchat->setReste($variable['stock_final_achat']);
        $this->achatRepository->save($dernierAchat, true);

        $produit->setStock((int) $variable['stock_final_produit']);
        $this->produitRepository->save($produit, true);

        $recette->setMontant($variable['montant_recette']);
        $this->recetteRepository->save($recette, true);

        $entity->setQuantite((int)$entity->getQuantite() + $variable['quantite']);
        $entity->setStockInitial((int)$entity->getStockInitial() + $variable['stock_initial']);
        $entity->setStockFinal((int)$entity->getStockFinal() + $variable['stock_final_produit']);
        $entity->setBenefice((int)$entity->getBenefice() + $variable['benefice_total']);
        $this->venteRepository->save($entity, true);

        return true;
    }

}
