<?php

namespace App\Controller;

use App\Entity\Recette;
use App\Repository\RecetteRepository;
use Flasher\Prime\Flasher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/validation')]
class RecetteValidationController extends AbstractController
{
    #[Route('/{id}', name: 'app_recette_validation', methods: ['POST'])]
    public function index(Request $request, Recette $recette, RecetteRepository $recetteRepository, Flasher $flasher): Response
    {
        //dd($request);
        //dd($request->request->get("_validation"));
        if ($this->isCsrfTokenValid('validation'.$recette->getId(), $request->request->get('_validation'))){
            $recette->setValidation(true);
            $recetteRepository->save($recette, true);
            $flasher->addSuccess("La validation a été effectuée avec succès!");
        }

        return $this->redirectToRoute('app_recette_index',[], Response::HTTP_SEE_OTHER);
    }
}
