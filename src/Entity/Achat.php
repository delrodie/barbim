<?php

namespace App\Entity;

use App\Repository\AchatRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: AchatRepository::class)]
#[UniqueEntity(fields: ["commande","produit"],message: "Echec, cet achat a déjà été ajouté!")]
class Achat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $quantite = null;

    #[ORM\Column(nullable: true)]
    private ?int $stockInitial = null;

    #[ORM\Column(nullable: true)]
    private ?int $stockFinal = null;

    #[ORM\Column(nullable: true)]
    private ?int $montant = null;

    #[ORM\Column(nullable: true)]
    private ?int $benefice = null;

    #[ORM\ManyToOne]
    private ?Produit $produit = null;

    #[ORM\ManyToOne(inversedBy: 'achats')]
    private ?Commande $commande = null;

    #[ORM\Column(nullable: true)]
    private ?int $prixUnitaire = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(?int $quantite): self
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getStockInitial(): ?int
    {
        return $this->stockInitial;
    }

    public function setStockInitial(?int $stockInitial): self
    {
        $this->stockInitial = $stockInitial;

        return $this;
    }

    public function getStockFinal(): ?int
    {
        return $this->stockFinal;
    }

    public function setStockFinal(?int $stockFinal): self
    {
        $this->stockFinal = $stockFinal;

        return $this;
    }

    public function getMontant(): ?int
    {
        return $this->montant;
    }

    public function setMontant(?int $montant): self
    {
        $this->montant = $montant;

        return $this;
    }

    public function getBenefice(): ?int
    {
        return $this->benefice;
    }

    public function setBenefice(?int $benefice): self
    {
        $this->benefice = $benefice;

        return $this;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): self
    {
        $this->produit = $produit;

        return $this;
    }

    public function getCommande(): ?Commande
    {
        return $this->commande;
    }

    public function setCommande(?Commande $commande): self
    {
        $this->commande = $commande;

        return $this;
    }

    public function getPrixUnitaire(): ?int
    {
        return $this->prixUnitaire;
    }

    public function setPrixUnitaire(?int $prixUnitaire): self
    {
        $this->prixUnitaire = $prixUnitaire;

        return $this;
    }
}
