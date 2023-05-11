<?php

namespace App\Controller;

use App\Entity\Brasserie;
use App\Form\BrasserieType;
use App\Repository\BrasserieRepository;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/brasserie')]
class BrasserieController extends AbstractController
{
    #[Route('/', name: 'app_brasserie_index', methods: ['GET','POST'])]
    public function index(Request $request, BrasserieRepository $brasserieRepository): Response
    {
        $brasserie = new Brasserie();
        $form = $this->createForm(BrasserieType::class, $brasserie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $brasserieRepository->save($brasserie, true);

            return $this->redirectToRoute('app_brasserie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('brasserie/index.html.twig', [
            'brasseries' => $brasserieRepository->findAll(),
            'brasserie' => $brasserie,
            'form' => $form,
        ]);
    }

    #[Route('/new', name: 'app_brasserie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, BrasserieRepository $brasserieRepository): Response
    {
        $brasserie = new Brasserie();
        $form = $this->createForm(BrasserieType::class, $brasserie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $brasserieRepository->save($brasserie, true);

            return $this->redirectToRoute('app_brasserie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('brasserie/new.html.twig', [
            'brasserie' => $brasserie,
            'form' => $form,
        ]);
    }

    #[Route('/{slug}', name: 'app_brasserie_show', methods: ['GET'])]
    public function show(Brasserie $brasserie, ProduitRepository $produitRepository): Response
    {
        return $this->render('brasserie/show.html.twig', [
            'brasserie' => $brasserie,
            'produits' => $produitRepository->findBy(['brasserie' => $brasserie],['nom' => "ASC"])
        ]);
    }

    #[Route('/{slug}/edit', name: 'app_brasserie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Brasserie $brasserie, BrasserieRepository $brasserieRepository): Response
    {
        $form = $this->createForm(BrasserieType::class, $brasserie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $brasserieRepository->save($brasserie, true);

            return $this->redirectToRoute('app_brasserie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('brasserie/edit.html.twig', [
            'brasseries' => $brasserieRepository->findAll(),
            'brasserie' => $brasserie,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_brasserie_delete', methods: ['POST'])]
    public function delete(Request $request, Brasserie $brasserie, BrasserieRepository $brasserieRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$brasserie->getId(), $request->request->get('_token'))) {
            $brasserieRepository->remove($brasserie, true);
        }

        return $this->redirectToRoute('app_brasserie_index', [], Response::HTTP_SEE_OTHER);
    }
}
