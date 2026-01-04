<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StockController extends AbstractController
{
    #[Route('/stock/produits', name: 'app_stock_produits')]
    public function index(): Response
    {
        // Rend le fichier templates/stock/index.html.twig
        return $this->render('stock/index.html.twig');
    }
}