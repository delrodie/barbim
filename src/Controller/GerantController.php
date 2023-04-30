<?php

namespace App\Controller;

use App\Entity\Gerant;
use App\Form\GerantType;
use App\Repository\GerantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/gerant')]
class GerantController extends AbstractController
{
    #[Route('/', name: 'app_gerant_index', methods: ['GET','POST'])]
    public function index(Request $request, GerantRepository $gerantRepository): Response
    {
        $gerant = new Gerant();
        $form = $this->createForm(GerantType::class, $gerant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $gerantRepository->save($gerant, true);

            return $this->redirectToRoute('app_gerant_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('gerant/index.html.twig', [
            'gerants' => $gerantRepository->findAll(),
            'gerant' => $gerant,
            'form' => $form,
        ]);
    }

    #[Route('/new', name: 'app_gerant_new', methods: ['GET', 'POST'])]
    public function new(Request $request, GerantRepository $gerantRepository): Response
    {
        $gerant = new Gerant();
        $form = $this->createForm(GerantType::class, $gerant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $gerantRepository->save($gerant, true);

            return $this->redirectToRoute('app_gerant_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('gerant/new.html.twig', [
            'gerant' => $gerant,
            'form' => $form,
        ]);
    }

    #[Route('/{slug}', name: 'app_gerant_show', methods: ['GET'])]
    public function show(Gerant $gerant): Response
    {
        return $this->render('gerant/show.html.twig', [
            'gerant' => $gerant,
        ]);
    }

    #[Route('/{slug}/edit', name: 'app_gerant_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Gerant $gerant, GerantRepository $gerantRepository): Response
    {
        $form = $this->createForm(GerantType::class, $gerant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $gerantRepository->save($gerant, true);

            return $this->redirectToRoute('app_gerant_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('gerant/edit.html.twig', [
            'gerants' => $gerantRepository->findAll(),
            'gerant' => $gerant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_gerant_delete', methods: ['POST'])]
    public function delete(Request $request, Gerant $gerant, GerantRepository $gerantRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$gerant->getId(), $request->request->get('_token'))) {
            $gerantRepository->remove($gerant, true);
        }

        return $this->redirectToRoute('app_gerant_index', [], Response::HTTP_SEE_OTHER);
    }
}
