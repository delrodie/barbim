<?php

namespace App\Controller;

use App\Entity\Recette;
use App\Form\RecetteType;
use App\Repository\RecetteRepository;
use App\Repository\VenteRepository;
use App\Utilities\AllRepository;
use Flasher\Prime\Flasher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/recette')]
class RecetteController extends AbstractController
{
    public function __construct(
        private Flasher $flasher, private AllRepository $allRepository, private RecetteRepository $recetteRepository
    )
    {
    }

    #[Route('/', name: 'app_recette_index', methods: ['GET','POST'])]
    public function index(Request $request, RecetteRepository $recetteRepository): Response
    {
        $recette = new Recette();
        $form = $this->createForm(RecetteType::class, $recette);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($this->validation($recette)) return $this->redirectToRoute('app_recette_index');

            $recetteRepository->save($recette, true);

            return $this->redirectToRoute('app_recette_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('recette/index.html.twig', [
            'recettes' => $recetteRepository->findBy([],["dateRecette" => "DESC"]),
            'recette' => $recette,
            'form' => $form,
        ]);
    }

    #[Route('/new', name: 'app_recette_new', methods: ['GET', 'POST'])]
    public function new(Request $request, RecetteRepository $recetteRepository): Response
    {
        $recette = new Recette();
        $form = $this->createForm(RecetteType::class, $recette);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $recetteRepository->save($recette, true);

            return $this->redirectToRoute('app_recette_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('recette/new.html.twig', [
            'recette' => $recette,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_recette_show', methods: ['GET'])]
    public function show(Recette $recette, VenteRepository $venteRepository): Response
    {
        return $this->render('recette/show.html.twig', [
            'recette' => $recette,
            'ventes' => $this->allRepository->getVenteByRecette($recette->getId())
        ]);
    }

    #[Route('/{id}/edit', name: 'app_recette_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Recette $recette, RecetteRepository $recetteRepository): Response
    {
        $form = $this->createForm(RecetteType::class, $recette);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $recetteRepository->save($recette, true);

            return $this->redirectToRoute('app_recette_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('recette/edit.html.twig', [
            'recettes' => $recetteRepository->findBy([],["dateRecette" => "DESC"]),
            'recette' => $recette,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_recette_delete', methods: ['POST'])]
    public function delete(Request $request, Recette $recette, RecetteRepository $recetteRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$recette->getId(), $request->request->get('_token'))) {
            $recetteRepository->remove($recette, true);
        }

        return $this->redirectToRoute('app_recette_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Vérification de la validité de la recette.
     * Si la date selectionnée est supérieure à a date du jour alors echec
     * Si la dernière recette n'a pas été validée alors echec
     *
     * @param object $recette
     * @return bool
     */
    public function validation(object $recette): bool
    {
        // Vérification de la date de vente
        // Si elle est supérieure à la date du jour alors echec
        $today = new \DateTime();
        $date_recette = \DateTime::createFromFormat('Y-m-d', $recette->getDateRecette()->format('Y-m-d')) ;
        $diff = $today->diff($date_recette);
        if ($diff->invert === 0){
            $this->flasher->addError("Echec, La date saisie n'est pas valide!");
            return true;
        }

        // Vérification du statut de la dernière recette
        // Si elle n'a pas été validée alors echec d'enregistrement de la nouvelle
        $derniereVente = $this->recetteRepository->findOneBy([],['id'=>"DESC"]);
        if (!$derniereVente->isValidation()){
            $this->flasher->addError("Echec, veuillez valider la dernière recette avant d'enregistrer une nouvelle");
            return true;
        }

        return false;
    }
}
