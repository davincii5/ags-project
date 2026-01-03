<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard')]
    public function admin(): Response
    {
        return $this->render('dashboard/admin.html.twig');
    }

    #[Route('/manager', name: 'manager_dashboard')]
    public function manager(): Response
    {
        return $this->render('dashboard/manager.html.twig');
    }

    #[Route('/stock', name: 'stock_dashboard')]
    public function stock(): Response
    {
        return $this->render('dashboard/stock.html.twig');
    }
}
