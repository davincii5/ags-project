<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FournisseurController extends AbstractController
{
    #[Route('/fournisseurs', name: 'app_fournisseurs')]
    public function index(): Response
    {
        return $this->render('fournisseur/index.html.twig');
    }
}