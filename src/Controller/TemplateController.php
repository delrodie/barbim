<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/template')]
class TemplateController extends AbstractController
{
    #[Route('/', name: 'app_template')]
    public function index(): Response
    {
        return $this->render('template/index.html.twig', [
            'controller_name' => 'TemplateController',
        ]);
    }

    #[Route('/formulaire', name: 'app_template_form')]
    public function form()
    {
        return $this->render('template/form.html.twig', []);
    }
}
