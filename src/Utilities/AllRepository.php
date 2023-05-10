<?php

namespace App\Utilities;

use App\Repository\ProduitRepository;
use App\Repository\VenteRepository;

class AllRepository
{
    public function __construct(
        private VenteRepository $venteRepository, private ProduitRepository $produitRepository
    )
    {
    }

    public function getVenteByRecette($recette)
    {
        return $this->venteRepository->findByRecette($recette);
    }

    public function getAllProduits()
    {
        return $this->produitRepository->findAllProduits();
    }

    public function verifStockProduit(object $vente): bool
    {
        // Si la quantité restante est supérieure à la quantité finale alors echec
        if ((int) $vente->getStockFinal() >= (int) $vente->getProduit()->getStock()) return true;

        /*
        // Calcul de la quantité vendue
        $prixVente = $vente->getProduit()->getMontant() ?? 1;
        $quantite = (int) $vente->getMontant() / (int) $prixVente;

        // Si la quantité vendue est supérieure à la quantité en stock alors echec
        if ($quantite > (int)$vente->getProduit()->getStock()) return true;
        */
        return false;
    }
}