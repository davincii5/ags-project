<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * Trouve les produits dont le stock est inférieur ou égal au seuil d'alerte
     * @return Product[]
     */
    public function findLowStockProducts(): array
    {
        return $this->createQueryBuilder('p')
            // On compare la quantité actuelle au seuil défini pour chaque produit
            ->andWhere('p.quantity <= p.alertThreshold')
            // On trie par quantité croissante (les stocks à 0 en premier)
            ->orderBy('p.quantity', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * (Optionnel) Trouve les produits les plus chers pour l'analyse de stock
     * @return Product[]
     */
    public function findMostExpensiveProducts(int $limit = 5): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.purchasePrice', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}