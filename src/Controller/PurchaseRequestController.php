<?php

namespace App\Controller;

use App\Entity\PurchaseRequest;
use App\Form\PurchaseRequestType;
use App\Repository\PurchaseRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/purchase/request')]
final class PurchaseRequestController extends AbstractController
{
    // ======================
    // LIST / INDEX
    // ======================
    #[Route(name: 'app_purchase_request_index', methods: ['GET'])]
    public function index(PurchaseRequestRepository $repo): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        if ($this->isGranted('ROLE_ADMIN')) {
            $requests = $repo->findAll();
        } elseif ($this->isGranted('ROLE_MANAGER')) {
            $requests = $repo->findBy(['requestedBy' => $user]);
        } else {
            // Magasinier → lecture seule
            $requests = $repo->findAll();
        }

        return $this->render('purchase_request/index.html.twig', [
            'purchase_requests' => $requests,
        ]);
    }

    // ======================
    // CREATE
    // ======================
    #[Route('/new', name: 'app_purchase_request_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // ✅ Admin & Manager seulement
        $this->denyAccessUnlessGranted('ROLE_MANAGER');

        $purchaseRequest = new PurchaseRequest();
        $purchaseRequest->setRequestedBy($this->getUser());

        $form = $this->createForm(PurchaseRequestType::class, $purchaseRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($purchaseRequest);
            $entityManager->flush();

            return $this->redirectToRoute('app_purchase_request_index');
        }

        return $this->render('purchase_request/new.html.twig', [
            'form' => $form,
        ]);
    }

    // ======================
    // SHOW
    // ======================
    #[Route('/{id}', name: 'app_purchase_request_show', methods: ['GET'])]
    public function show(PurchaseRequest $purchaseRequest): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('purchase_request/show.html.twig', [
            'purchase_request' => $purchaseRequest,
        ]);
    }

    // ======================
    // EDIT
    // ======================
    #[Route('/{id}/edit', name: 'app_purchase_request_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PurchaseRequest $purchaseRequest, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_MANAGER');

        // ❌ Manager ne peut modifier que ses propres demandes
        if (
            !$this->isGranted('ROLE_ADMIN') &&
            $purchaseRequest->getRequestedBy() !== $this->getUser()
        ) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }

        $form = $this->createForm(PurchaseRequestType::class, $purchaseRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_purchase_request_index');
        }

        return $this->render('purchase_request/edit.html.twig', [
            'form' => $form,
            'purchase_request' => $purchaseRequest,
        ]);
    }

    // ======================
    // DELETE
    // ======================
    #[Route('/{id}', name: 'app_purchase_request_delete', methods: ['POST'])]
    public function delete(Request $request, PurchaseRequest $purchaseRequest, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_MANAGER');

        if (
            !$this->isGranted('ROLE_ADMIN') &&
            $purchaseRequest->getRequestedBy() !== $this->getUser()
        ) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }

        if ($this->isCsrfTokenValid('delete'.$purchaseRequest->getId(), $request->request->get('_token'))) {
            $entityManager->remove($purchaseRequest);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_purchase_request_index');
    }
    
    // ======================
    // WORKFLOW: APPROVE
    // ======================
    #[Route('/{id}/approve', name: 'app_purchase_request_approve', methods: ['POST'])]
    public function approve(Request $request, PurchaseRequest $purchaseRequest, EntityManagerInterface $entityManager): Response
    {
        // 🔒 Seul l'Admin peut valider
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // 1. Vérifier qu'on ne valide pas deux fois
        if ($purchaseRequest->getStatus() !== 'pending') {
            $this->addFlash('warning', 'Cette demande a déjà été traitée.');
            return $this->redirectToRoute('app_purchase_request_index');
        }

        // 2. Vérification CSRF (Sécurité bouton)
        if ($this->isCsrfTokenValid('approve'.$purchaseRequest->getId(), $request->request->get('_token'))) {
            
            // A. Changer le statut
            $purchaseRequest->setStatus('approved');

            // B. Mettre à jour le Stock du Produit
            $product = $purchaseRequest->getProduct();
            $newQuantity = $product->getQuantity() + $purchaseRequest->getQuantity();
            $product->setQuantity($newQuantity);

            // C. Créer le Mouvement de Stock (Traçabilité)
            // Assure-toi d'avoir importé l'entité StockMovement en haut du fichier !
            $movement = new \App\Entity\StockMovement();
            $movement->setProduct($product);
            $movement->setQuantity($purchaseRequest->getQuantity());
            $movement->setType('IN'); // Entrée de stock
            $movement->setCreatedAt(new \DateTimeImmutable());
            
            // Si tu as ajouté une relation User dans StockMovement, décommente la ligne ci-dessous :
            // $movement->setUser($this->getUser()); 

            $entityManager->persist($movement);
            $entityManager->flush();

            $this->addFlash('success', 'Demande approuvée : Stock mis à jour !');
        }

        return $this->redirectToRoute('app_purchase_request_index');
    }

    // ======================
    // WORKFLOW: REJECT
    // ======================
    #[Route('/{id}/reject', name: 'app_purchase_request_reject', methods: ['POST'])]
    public function reject(Request $request, PurchaseRequest $purchaseRequest, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($purchaseRequest->getStatus() !== 'pending') {
            $this->addFlash('warning', 'Cette demande a déjà été traitée.');
            return $this->redirectToRoute('app_purchase_request_index');
        }

        if ($this->isCsrfTokenValid('reject'.$purchaseRequest->getId(), $request->request->get('_token'))) {
            $purchaseRequest->setStatus('rejected');
            $entityManager->flush();
            
            $this->addFlash('danger', 'Demande refusée.');
        }

        return $this->redirectToRoute('app_purchase_request_index');
    }
}
