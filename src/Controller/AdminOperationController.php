<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminOperationController extends AbstractController
{
    #[Route('/admin/demandes', name: 'app_admin_demandes')]
    public function demandes(): Response
    {
        // On pointe vers templates/admin/demandes.html.twig
        return $this->render('admin/demandes.html.twig');
    }

    #[Route('/admin/tracabilite', name: 'app_admin_tracabilite')]
    public function tracabilite(): Response
    {
        // On pointe vers templates/admin/tracabilite.html.twig
        return $this->render('admin/tracabilite.html.twig');
    }
}