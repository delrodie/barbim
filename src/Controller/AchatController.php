<?php

namespace App\Controller;

use App\Entity\Achat;
use App\Entity\Commande;
use App\Form\AchatType;
use App\Repository\AchatRepository;
use App\Repository\CommandeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/achat')]
class AchatController extends AbstractController
{
    #[Route('/{id}', name: 'app_achat_index', methods: ['GET','POST'])]
    public function index(Request $request, Commande $commande, AchatRepository $achatRepository): Response
    {
        $achat = new Achat();
        $form = $this->createForm(AchatType::class, $achat, ['commande' => $commande]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $achatRepository->save($achat, true);

            return $this->redirectToRoute('app_achat_index', ['id' => $commande->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('achat/index.html.twig', [
            'achats' => $achatRepository->findBy(['commande' => $commande->getId()]),
            'achat' => $achat,
            'form' => $form,
            'commande' => $commande
        ]);
    }

    #[Route('/new', name: 'app_achat_new', methods: ['GET', 'POST'])]
    public function new(Request $request, AchatRepository $achatRepository): Response
    {
        $achat = new Achat();
        $form = $this->createForm(AchatType::class, $achat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $achatRepository->save($achat, true);

            return $this->redirectToRoute('app_achat_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('achat/new.html.twig', [
            'achat' => $achat,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_achat_show', methods: ['GET'])]
    public function show(Achat $achat): Response
    {
        return $this->render('achat/show.html.twig', [
            'achat' => $achat,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_achat_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Achat $achat, AchatRepository $achatRepository, CommandeRepository $commandeRepository): Response
    {
        $commande = $commandeRepository->findOneBy(['id' => $achat->getCommande()->getId()]);
        $form = $this->createForm(AchatType::class, $achat, ['commande' => $commande]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $achatRepository->save($achat, true);

            return $this->redirectToRoute('app_achat_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('achat/edit.html.twig', [
            'achats' => $achatRepository->findBy(['commande' => $commande->getId()]),
            'achat' => $achat,
            'form' => $form,
            'commande' => $commande
        ]);
    }

    #[Route('/{id}/delete', name: 'app_achat_delete', methods: ['POST'])]
    public function delete(Request $request, Achat $achat, AchatRepository $achatRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$achat->getId(), $request->request->get('_token'))) {
            $achatRepository->remove($achat, true);
        }

        return $this->redirectToRoute('app_achat_index', ['id' => $achat->getCommande()->getId()], Response::HTTP_SEE_OTHER);
    }
}
