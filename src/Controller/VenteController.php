<?php

namespace App\Controller;

use App\Entity\Recette;
use App\Entity\Vente;
use App\Form\VenteType;
use App\Repository\RecetteRepository;
use App\Repository\VenteRepository;
use App\Utilities\AllRepository;
use Flasher\Prime\Flasher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/vente')]
class VenteController extends AbstractController
{
    public function __construct(private AllRepository $allRepository, private Flasher $flasher)
    {
    }

    #[Route('/{id}', name: 'app_vente_index', methods: ['GET','POST'])]
    public function index(Request $request, Recette $recette, VenteRepository $venteRepository): Response
    {
        $vente = new Vente();
        $form = $this->createForm(VenteType::class, $vente, ['recette' => $recette]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if($this->allRepository->verifStockProduit($vente)){
               $this->flasher->addError("Echec, La quantité de '{$vente->getProduit()->getNom()}' vendue est supérieure au stock. Veuillez passer une commande d'abrod!");
               return $this->redirectToRoute('app_vente_index',['id' => $recette->getId()], Response::HTTP_SEE_OTHER);
            }
            $venteRepository->save($vente, true);

            return $this->redirectToRoute('app_vente_index', ['id' => $recette->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('vente/index.html.twig', [
            'ventes' => $venteRepository->findBy(['recette' => $recette->getId()]),
            'vente' => $vente,
            'form' => $form,
            'recette' => $recette
        ]);
    }

    #[Route('/new', name: 'app_vente_new', methods: ['GET', 'POST'])]
    public function new(Request $request, VenteRepository $venteRepository): Response
    {
        $vente = new Vente();
        $form = $this->createForm(VenteType::class, $vente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $venteRepository->save($vente, true);

            return $this->redirectToRoute('app_vente_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('vente/new.html.twig', [
            'vente' => $vente,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_vente_show', methods: ['GET'])]
    public function show(Vente $vente): Response
    {
        return $this->render('vente/show.html.twig', [
            'vente' => $vente,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_vente_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Vente $vente, VenteRepository $venteRepository, RecetteRepository $recetteRepository): Response
    {
        $form = $this->createForm(VenteType::class, $vente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $venteRepository->save($vente, true);

            return $this->redirectToRoute('app_vente_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('vente/edit.html.twig', [
            'vente' => $vente,
            'form' => $form,
            'recette' => $recetteRepository->findOneBy(['id' => $vente->getRecette()])
        ]);
    }

    #[Route('/{id}', name: 'app_vente_delete', methods: ['POST'])]
    public function delete(Request $request, Vente $vente, VenteRepository $venteRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$vente->getId(), $request->request->get('_token'))) {
            $venteRepository->remove($vente, true);
        }

        return $this->redirectToRoute('app_vente_index', [], Response::HTTP_SEE_OTHER);
    }
}
