<?php
// src/Controller/DashboardController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    /**
     * Cette route correspond au tableau de bord décisionnel demandé
     * pour l'Administrateur.
     */
    #[Route('/dashboard', name: 'admin_dashboard')]
    public function index(): Response
    {
        // En tant qu'Étudiant 3, tu prépares l'affichage Twig.
        return $this->render('dashboard/index.html.twig');
    }
    #[Route('/worker', name: 'app_worker_dashboard')]
public function workerDashboard(): Response
{
    // On pourra plus tard passer les dernières opérations effectuées
    return $this->render('dashboard/worker.html.twig');
}
// ... après ta fonction workerDashboard()

    #[Route('/manager', name: 'app_manager_dashboard')]
    public function managerDashboard(): Response
    {
        // Interface pour la supervision et les alertes stock
        return $this->render('dashboard/manager.html.twig');
    }
}