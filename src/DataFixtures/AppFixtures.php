<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\PurchaseRequest;
use App\Entity\Supplier;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $userRepo = $manager->getRepository(User::class);

        // ======================================================
        // 1. GESTION DES UTILISATEURS (Récupération ou Création)
        // ======================================================

        // --- ADMIN ---
        $admin = $userRepo->findOneBy(['email' => 'admin@test.com']);
        if (!$admin) {
            $admin = new User();
            $admin->setEmail('admin@test.com');
            $admin->setRoles(['ROLE_ADMIN']);
            $admin->setPassword($this->hasher->hashPassword($admin, '123456'));
            $manager->persist($admin);
        }

        // --- MANAGER (Important pour lier les commandes) ---
        $managerUser = $userRepo->findOneBy(['email' => 'manager@test.com']);
        if (!$managerUser) {
            $managerUser = new User();
            $managerUser->setEmail('manager@test.com');
            $managerUser->setRoles(['ROLE_MANAGER']);
            $managerUser->setPassword($this->hasher->hashPassword($managerUser, 'manager123456'));
            $manager->persist($managerUser);
        }

        // --- MAGASINIER ---
        $magasinier = $userRepo->findOneBy(['email' => 'magasinier@test.com']);
        if (!$magasinier) {
            $magasinier = new User();
            $magasinier->setEmail('magasinier@test.com');
            $magasinier->setRoles(['ROLE_MAGASINIER']);
            $magasinier->setPassword($this->hasher->hashPassword($magasinier, 'magasinier123456'));
            $manager->persist($magasinier);
        }

        // ======================================================
        // 2. CRÉATION DES FOURNISSEURS
        // ======================================================
        $suppliers = [];
        $supplierNames = ['Dell Maroc', 'Samsung Electronics', 'Logitech Pro', 'Bureau Vallée'];
        foreach ($supplierNames as $name) {
            $supplier = new Supplier();
            $supplier->setName($name);
            $supplier->setEmail(strtolower(str_replace(' ', '', $name)) . '@contact.com');
            $supplier->setPhone('0522000000');
            $manager->persist($supplier);
            $suppliers[] = $supplier;
        }

        // ======================================================
        // 3. CRÉATION DES PRODUITS (Avec Alertes !)
        // ======================================================
        $products = [];
        // Format : [Nom, Ref, Prix Achat, Prix Vente, Quantité, Seuil Alerte]
        $productData = [
            ['HP EliteBook', 'PC-HP-001', 8000, 10000, 5, 10], // ⚠️ ALERTE (5 < 10)
            ['Dell Latitude', 'PC-DELL-002', 7500, 9500, 20, 5],
            ['Ecran Samsung 24"', 'ECR-SAM-24', 1200, 1800, 2, 8], // ⚠️ ALERTE
            ['Clavier Mécanique', 'ACC-CLAV-01', 300, 500, 50, 10],
            ['Souris Logitech', 'ACC-SOU-02', 150, 250, 100, 20],
            ['Imprimante Canon', 'IMP-CAN-01', 2500, 3500, 1, 3], // ⚠️ ALERTE
            ['Papier A4 (Rame)', 'BUR-PAP-A4', 40, 60, 200, 50],
            ['Disque SSD 1TB', 'STO-SSD-1T', 600, 900, 15, 5],
            ['Câble HDMI 2m', 'CAB-HDMI-2', 50, 100, 30, 10],
            ['Chaise Ergonomique', 'MOB-CHAISE', 1500, 2500, 4, 5], // ⚠️ ALERTE
        ];

        foreach ($productData as $data) {
            $product = new Product();
            $product->setName($data[0]);
            $product->setReference($data[1]);
            $product->setPurchasePrice($data[2]);
            $product->setSalesPrice($data[3]);
            $product->setQuantity($data[4]); 
            $product->setAlertThreshold($data[5]);
            
            $manager->persist($product);
            $products[] = $product;
        }

        // ======================================================
        // 4. CRÉATION DES DEMANDES D'ACHAT (Liées au Manager)
        // ======================================================
        
        // Commandes EN ATTENTE (Pour ta "To-Do List")
        for ($i = 0; $i < 5; $i++) {
            $req = new PurchaseRequest();
            $req->setProduct($products[array_rand($products)]);
            $req->setRequestedBy($managerUser); // Utilise ton manager existant
            $req->setSupplier($suppliers[array_rand($suppliers)]);
            $req->setQuantity(rand(10, 50));
            $req->setJustification("Besoin urgent stock critique");
            $req->setStatus('pending');
            $manager->persist($req);
        }

        // Commandes VALIDÉES (Pour l'historique)
        for ($i = 0; $i < 5; $i++) {
            $req = new PurchaseRequest();
            $req->setProduct($products[array_rand($products)]);
            $req->setRequestedBy($managerUser);
            $req->setSupplier($suppliers[array_rand($suppliers)]);
            $req->setQuantity(rand(5, 20));
            $req->setJustification("Réassort mensuel standard");
            $req->setStatus('approved');
            $manager->persist($req);
        }

        $manager->flush();
    }
}